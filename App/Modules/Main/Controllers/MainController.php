<?php

namespace Main;

use J\Controller;
use J;
use J\NeoNode;

class MainController extends Controller {
	public function indexAction($isim) {
		//$node = NeoNode::getWithId(1);
		$node = NeoNode::create('User');
		$result = $node->delete();
		//$result2 = J::neo()->select('match n return n');
		$this->render(['result'=>[$result]]);
	}
	public function merhabaDeAction($isim1,$isim2) {
		echo 'Merhaba'. $isim1.' '.$isim2;
	}
}