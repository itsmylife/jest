<?php

namespace J;
use J;

class Controller {
	public function render($params=[]) {
		$controllerName = str_replace('Controller','',J::$controllerName);
		echo J::templater()->render(J::path(J::$moduleName.'/Views/'.$controllerName),J::$actionName,$params);
	}
	
	public function renderView($params,$view) {
		
	}
}