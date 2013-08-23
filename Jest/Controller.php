<?php

namespace J;
use J;

class Controller {
	
	public $layout;
	
	public function __construct() {
		$this->layout = J::$options['layout'];
	}
	
	public function render($params=[]) {
		echo J::templater()->renderWithLayout($params,$this->layout);
	}
	
	public function renderView($params,$view) {
		
	}
}