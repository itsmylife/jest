<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 02.09.2013
 * Time: 17:02
 * To change this template use File | Settings | File Templates.
 */

namespace J;


class FormField {
	public $form;
	public $label;
	public $attr;
	public $type;
	public $labelAttr;
	public $value;
	public $name;
	public $constraints;
	private $typeMap;
	public $error;
	
	public function __construct($name,$opt,$value = null) {
		$this->name = $name;
		$this->label = (isset($opt['label']))?$opt['label']:null;
		$this->attr = (isset($opt['attr']))?$opt['attr']:'';
		$this->type = (isset($opt['type']))?$opt['type']:null;
		$this->value = $value;
		$this->labelAttr = (isset($opt['labelAttr']))?$opt['labelAttr']:null;
		$this->constraints = (isset($opt['constraints']))?$opt['constraints']:null;
		$this->typeMap = [
			'textField'=>'<input type="text" id="|_id_|" name="|_name_|" value="|_value_|" |_attr_|/>',
			'passwordField'=>'<input type="password" id="|_id_|" name="|_name_|" value="|_value_|" |_attr_|/>',
			'submitButton'=>'<input type="submit" id="|_id_|" name="|_name_|" value="|_value_|" |_attr_|/>'
		];
	}
	public function render() {
		if ($this->type) {
			$template = $this->typeMap[$this->type];
			$attrs = '';
			if (is_array($this->attr)) {
				foreach($this->attr as $attr=>$attrValue) {
					$attrs .= $attr.'="'.htmlspecialchars($attrValue).'" ';
				}
			}
			$field = str_replace('|_id_|',$this->form.'_'.$this->name,$template);
			$field = str_replace('|_name_|',$this->form.'['.$this->name.']',$field);
			$field = str_replace('|_value_|',$this->value,$field);
			return str_replace('|_attr_|',$attrs,$field);
		} else return false;		
	}
	
	public function renderLabel() {
		if ($this->label) {
			return '<label for="'.$this->form.'_'.$this->name.'" >'.$this->label.'</label>';
		} else return false;
	}
	
	public function renderError() {
		if ($this->error) {
			return $this->error;
		} else return false;
	}
}