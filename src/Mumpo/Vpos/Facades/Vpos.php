<?php

namespace Mumpo\Vpos\Facades;

use Illuminate\Support\Facades\Facade;

class Vpos extends Facade {
	/**
	 * Get the facade accessor.
	 *
	 * @return      string      Facade accessor
	 */
	protected static function getFacadeAccessor() { return 'vpos'; }
}
