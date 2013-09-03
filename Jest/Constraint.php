<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 03.09.2013
 * Time: 13:26
 * To change this template use File | Settings | File Templates.
 */

namespace J;
use J;

use Member\Member;

class Constraint {
	private static $instance;
	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function checkRequired($field,$constraint,$data,$model) {
		$value = $field->value;
		if (!empty($value)) return true;
		return false;
	}
	
	public function checkUnique($field,$constraint,$data,$model) {
		/** @var Member $className */
		$className = get_class($model);
		return true;
	}
	
	public function checkSame($field,$constraint,$data,$model) {
		$value = $field->value;
		$sameValue = $data[$constraint['with']];
		return ((string)$value == (string)$sameValue);
	}
	
	public function checkSafe($field,$constraint,$data,$model) {
		return true;
	}
}