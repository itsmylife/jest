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
		if ($this->checkEmpty($constraint,$field->value)) return true;
		/** @var Member $className */
		$className = get_class($model);
		$result = $className::initQuery()
			->addMatch('this')
			->addWhere('this.'.$field->name.'={fieldValue}')
			->addParameters(['fieldValue'=>$field->value])
			->count();
		return ($result<1);
	}
	
	public function checkEmail($field,$constraint,$data,$model) {
		if ($this->checkEmpty($constraint,$field->value)) return true;
		return preg_match('/^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,4}$/i', $field->value);
	}
	
	public function checkSame($field,$constraint,$data,$model) {
		if ($this->checkEmpty($constraint,$field->value)) return true;
		$value = $field->value;
		$sameValue = $data[$constraint['with']];
		return ((string)$value == (string)$sameValue);
	}
	
	public function checkSafe($field,$constraint,$data,$model) {
		if ($this->checkEmpty($constraint,$field->value)) return true;
		return true;
	}
	
	public function allowEmpty($constraint) {
		if (isset($constraint['allowEmpty']) && !$constraint['allowEmpty']) return false;
		else return true;
	}
	
	public function checkEmpty($constraint,$value) {
		if (empty($value) && $this->allowEmpty($constraint)) return true;
		return false;
	}
}