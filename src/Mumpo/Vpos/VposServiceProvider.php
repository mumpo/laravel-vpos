<?php

namespace Mumpo\Vpos;

use Illuminate\Support\ServiceProvider;

class VposServiceProvider extends ServiceProvider {
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
		//
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		// $this->app->singleton( 'vpos', 'Mumpo\Vpos\Vpos' );
		// $viewPath = __DIR__ . '/views/';
		//
		// $this->loadViewsFrom($viewPath, 'vpos');

		$this->app['vpos'] = $this->app->share(function($app)
		{
			$vpos = $this->app->make( 'Mumpo\Vpos\Vpos' );

			$viewPath = __DIR__ . '/views/';

			$this->loadViewsFrom($viewPath, 'vpos');

			return $vpos;
		});
	}
}
