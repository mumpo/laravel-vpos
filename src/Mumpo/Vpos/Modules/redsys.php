<?php

namespace Mumpo\Vpos\Modules;

use Mumpo\Vpos\VposInterface;
use Mumpo\Vpos\Exceptions\VposParameterException;

class Redsys implements VposInterface {

	private $view;

	public function __construct($view) {
		$this->view = $view;
	}

	public function payment($data, $options) {
		$order = $this->requireParam('order', $data);
		$mp = array(
			"DS_MERCHANT_AMOUNT" => $this->requireParam('amount', $data),
			"DS_MERCHANT_ORDER" => $order,
			"DS_MERCHANT_MERCHANTCODE" => $this->requireParam('merchant.merchantcode', $options),
			"DS_MERCHANT_CURRENCY" => $this->requireParam('merchant.currency', $options),
			"DS_MERCHANT_TRANSACTIONTYPE" => $this->requireParam('merchant.transactiontype', $options),
			"DS_MERCHANT_TERMINAL" => $this->requireParam('merchant.terminal', $options),
			"DS_MERCHANT_MERCHANTURL" => $this->requireParam('asyncUrl', $data),
			"DS_MERCHANT_URLOK" => $this->requireParam('okUrl', $data),
			"DS_MERCHANT_URLKO" => $this->requireParam('koUrl', $data)
		);

		$version = $this->requireParam('version', $options);
		$params = $this->createMerchantParameters($mp);
		$kc = $this->requireParam('merchant.signature', $options);
		$signature = $this->createMerchantSignature($order, $params, $kc);

		return $this->view->render(array(
			'url' => $this->requireParam('url', $options),
			'inputs' => array(
				"Ds_SignatureVersion" => $version,
				"Ds_MerchantParameters" => $params,
				"Ds_Signature" => $signature
			)
		));
	}

	private function requireParam($param, $data) {
		$parts = explode(".", $param);
		foreach ($parts as $part) {
			if (!isset($data[$part])) throw new VposParameterException("Parameter [$param] not found.");
			$data = $data[$part];
		}
		return $data;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	////////////					FUNCIONES AUXILIARES:							  ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////


	/******  3DES Function  ******/
	private function encrypt_3DES($message, $key){
		// Se establece un IV por defecto
		$bytes = array(0,0,0,0,0,0,0,0); //byte [] IV = {0, 0, 0, 0, 0, 0, 0, 0}
		$iv = implode(array_map("chr", $bytes)); //PHP 4 >= 4.0.2

		// Se cifra
		$ciphertext = mcrypt_encrypt(MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv); //PHP 4 >= 4.0.2
		return $ciphertext;
	}

	/******  Base64 Functions  ******/
	private function base64_url_encode($input){
		return strtr(base64_encode($input), '+/', '-_');
	}
	private function base64_url_decode($input){
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/******  MAC Function ******/
	private function mac256($ent,$key){
		$res = hash_hmac('sha256', $ent, $key, true);//(PHP 5 >= 5.1.2)
		return $res;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	////////////	   FUNCIONES PARA LA GENERACIÓN DEL FORMULARIO DE PAGO:			  ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////

	private function createMerchantParameters($params){
		// Se transforma el array de datos en un objeto Json
		$json = json_encode($params);
		// Se codifican los datos Base64
		return base64_encode($json);
	}
	private function createMerchantSignature($order, $params, $key){
		// Se decodifica la clave Base64
		$key = base64_decode($key);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($order, $key);
		// MAC256 del parámetro Ds_MerchantParameters
		$res = $this->mac256($params, $key);
		// Se codifican los datos Base64
		return base64_encode($res);
	}



	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////// FUNCIONES PARA LA RECEPCIÓN DE DATOS DE PAGO (Notif, URLOK y URLKO): ////////////
	//////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////

	/******  Obtener Número de pedido ******/
	private function getOrderNotif(){
		$numPedido = "";
		if(empty($this->vars_pay['Ds_Order'])){
			$numPedido = $this->vars_pay['DS_ORDER'];
		} else {
			$numPedido = $this->vars_pay['Ds_Order'];
		}
		return $numPedido;
	}

	/******  Convertir String en Array ******/
	private function stringToArray($datosDecod){
		$this->vars_pay = json_decode($datosDecod, true); //(PHP 5 >= 5.2.0)
	}
	private function decodeMerchantParameters($datos){
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		return $decodec;
	}
	private function createMerchantSignatureNotif($key, $datos){
		// Se decodifica la clave Base64
		$key = base64_decode($key);
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		// Los datos decodificados se pasan al array de datos
		$this->stringToArray($decodec);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrderNotif(), $key);
		// MAC256 del parámetro Ds_Parameters que envía Redsys
		$res = $this->mac256($datos, $key);
		// Se codifican los datos Base64
		return $this->base64_url_encode($res);
	}

}
