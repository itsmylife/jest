<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 02.09.2013
 * Time: 14:14
 * To change this template use File | Settings | File Templates.
 */

namespace J;
use J;

/**
 * Class Form
 * @package J
 * @property FormField[] $fields
 */
class Form {
	public $model;
	public $name;
	public $fields;
	public $rowTemplate;
	public $method;
	public $data;
	public $errors;
	
	public function __construct($model) {
	$this->model = $model;
	$this->buildFields();
}
	
	public function isValid() {
		if (!$this->isSubmitted()) return false;	
		$errMessages = include_once(J::path('Jest/Messages/'.J::$options['lang'].'/constraint.php'));
		$valid = true;
		foreach ($this->fields as $field) {
			$field->value = (!empty($this->data[$field->name]))?$this->data[$field->name]:null;
			$this->model->{$field->name} = $field->value;
			foreach($field->constraints as $constraint) {
				$constraint['type'] = Helper::camelize($constraint['type'],true);
				if(!J::constraint()->{'check'.$constraint['type']}($field,$constraint,$this->data,$this->model)){
					$valid = false;
					$field->error = (isset($constraint['message']))?
						$constraint['message']:
						str_replace('{{attribute}}',$field->label,$errMessages[strtolower($constraint['type'])]);
					$this->errors[$field->name][] = $field->error;
				}
			}
		}
		return $valid;
	}
	
	public function buildFields() {
		$fieldOpts = $this->model->fields;
		foreach ($fieldOpts as $fieldName=>$opt) {
			$formField = new FormField($fieldName,$opt,(isset($opt['value']))?$opt['value']:null);
			$formField->form = $this->name;
			if (isset($opt['constraints'])) {
				$showField = false;
				foreach ($opt['constraints'] as $constraint) {
					if (isset($constraint['on'])) {
						$ons = explode(',',$constraint['on']);
						$ons = array_map('trim',$ons);
						if (in_array($this->name,$ons) && $constraint['type']!='notSafe') {
							$showField = true;
						}
					}
				}
				if ($showField) {
					$this->fields[$fieldName] = $formField;
				}
			}
		}
	}
	
	public function renderRow($fieldName) {
		if (isset($this->fields[$fieldName])) {
			/** @var FormField $field */
			$field = $this->fields[$fieldName];
			$rowData['label'] = $field->renderLabel();
			$rowData['field'] = $field->render();
			$rowData['error'] = $field->renderError();
			return J::templater()->render($rowData,$this->rowTemplate);
		} else {
			throw new Exception('Fieldname cannot be found:'.$fieldName);
		}
	}
	
	public function renderField($fieldName) {
		/** @var FormField $field */
		$field = $this->fields[$fieldName];
		return $field->render();
	}
	
	public function renderLabel($fieldName) {
		/** @var FormField $field */
		$field = $this->fields[$fieldName];
		return $field->renderLabel();
	}
	
	public function renderError($fieldName) {
		/** @var FormField $field */
		$field = $this->fields[$fieldName];
		return $field->renderError();
	}
	
	public function isSubmitted() {
		$method = strtolower($this->method);
		if ($method=="post") {
			if(isset($_POST[$this->name])) {
				$this->data = $_POST[$this->name];
				return true;
			}
		}
		if ($method=="get") {
			if(isset($_GET[$this->name])) {
				$this->data = $_GET[$this->name];
				return true;
			}
		}
		return false;
	}
	
	public function start() {
		return '<form method="'.$this->method.'" action="" >';
	}
	
	public function end() {
		return '</form>';
	}
	
}