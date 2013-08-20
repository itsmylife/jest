<?php

namespace J;

/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 17.08.2013
 * Time: 09:29
 * To change this template use File | Settings | File Templates.
 */

class Exception extends \Exception {
	public function __construct($message, $type=null) {
		return parent::__construct($message);
	}
}