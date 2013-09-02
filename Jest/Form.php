<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 02.09.2013
 * Time: 14:14
 * To change this template use File | Settings | File Templates.
 */

namespace J;


class Form {
	public $fields;
	
	public function isValid() {
		return true;
	}
	
	public function buildFields() {
		$fieldOpts = $this->fields;
		foreach ($fieldOpts as $fieldName=>$opt) {
			$formField = new FormField($fieldName,$opt);
			echo $formField->render();
		}
	}
	
	public function render() {
		$this->buildFields();
	}
}