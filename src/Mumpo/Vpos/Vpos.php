<?php namespace Mumpo\Vpos;

use Illuminate\Support\Facades\Config;
use Mumpo\Vpos\VposView;
use Mumpo\Vpos\Exceptions\VposNotFoundException;

class Vpos {

	/**
	 * The URL of the modules directory
	 *
	 * @var   string
	 */
	protected $package_url  = 'vendor/mumpo/laravel-vpos/src/Mumpo/Vpos/Modules';
	/**
	 * Variable holding the current vpos implementation.
	 *
	 * @var   array
	 */
	protected $implementation = null;
	/**
	 * Url to access the vpos
	 *
	 * @var   array
	 */
	protected $url = null;
	/**
	 * Vpos version (for encryption)
	 *
	 * @var   array
	 */
	protected $version = null;
	/**
	 * Merchant data
	 *
	 * @var   array
	 */
	protected $merchant = null;

	public function __construct(VposView $view) {
		// Get the Vpos implementation
		$imp = Config::get('services.vpos.implementation');
		$imp_path = base_path($this->package_url . '/' . $imp . '.php');
		if (!file_exists($imp_path)) {
			throw new VposNotFoundException();
		}
		require_once($imp_path);
		$imp_class = 'Mumpo\\Vpos\\Modules\\' . ucfirst($imp);
		$this->implementation = new $imp_class($view);

		// Gather configuration
		$this->url =  Config::get('services.vpos.url');
		$this->version = Config::get('services.vpos.version');
		$this->merchant = Config::get('services.vpos.merchant');
	}

	public function payment($data, $custom_options = array()) {
		$base_options = array(
			'url' => $this->url,
			'version' => $this->version,
			'merchant' => $this->merchant
		);
		$merged_options = array_merge_recursive($base_options, $custom_options);

		return $this->implementation->payment($data, $merged_options);
	}
}
