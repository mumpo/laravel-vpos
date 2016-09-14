<?php namespace App\Services;

use Illuminate\Support\Facades\Config;

class Vpos {

	/**
	 * Variable holding the current vpos implementation.
	 *
	 * @var   array
	 */
	protected $implementation = null;

	public function __construct() {
		$this->implementation = Config::get('services.vpos.implementation');
	}


}
