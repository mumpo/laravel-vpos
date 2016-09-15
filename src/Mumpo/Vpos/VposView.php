<?php

namespace Mumpo\Vpos;

use Illuminate\Contracts\View\Factory as ViewFactory;

class VposView {

	protected $factory;

	public function __construct(ViewFactory $factory)
	{
		$this->factory = $factory;
	}

	public function render($vpos)
	{
		return $this->factory->make("vpos::form", compact('vpos'))->render();
	}

}
