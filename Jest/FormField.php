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
	public $typeMap;
	public function __construct($name,$opt,$value = null) {
		$this->name = $name;
		$this->label = (isset($opt['label']))?$opt['label']:null;
		$this->attr = (isset($opt['attr']))?$opt['attr']:'';
		$this->type = (isset($opt['type']))?$opt['type']:null;
		$this->value = $value;
		$this->labelAttr = (isset($opt['labelAttr']))?$opt['labelAttr']:null;
		$this->typeMap = [
			'textField'=>'<input type="text" id="|_id_|" name="|_name_|" value="|_value_|" |_attr_|/>',
			'passwordField'=>'<input type="password" id="|_id_|" name="|_name_|" value="|_value_|" |_attr_|/>',
		];
	}
	public function render() {
		$template = $this->typeMap[$this->type];
		$attrs = '';
		if (is_array($this->attr)) {
			foreach($this->attr as $attr=>$attrValue) {
				$attrs .= $attr.'="'.htmlspecialchars($attrValue).'" ';
			}
		}		
		$field = preg_replace('/|_id_|/',$this->form.'_'.$this->name,$template);
		$field = preg_replace('/|_name_|/',$this->name,$field);
		$field = preg_replace('/|_value_|/',$this->value,$field);
		return preg_replace('/|_attr_|/',$attrs,$field);				
	}
}