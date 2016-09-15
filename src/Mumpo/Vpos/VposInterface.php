<?php

namespace Mumpo\Vpos;

interface VposInterface {

	public function payment($data, $options);
}
