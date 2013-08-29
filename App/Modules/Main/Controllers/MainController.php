<?php

namespace Main;

use J\Controller;
use J;
use J\NeoNode;

class MainController extends Controller {
	public function indexAction($isim) {
		//$node = NeoNode::getWithId(300);
		//$result1=$node->name;
		J::neo()->clearGraph();
		/*$node = NeoNode::create('Person',[
				'name'=>'Onur Eren', 'surname'=>'Elibol'
			]);*/
		
		//$result2 = J::neo()->select('match n return n');
		
		$onur = new Member();
		$onur->isim = 'Onur Eren';
		$onur->soyad = 'Elibol';
		$onur->username = 'codemaster';
		$onur->email = 'onurelibol@gmail.com';
		$onur->password = '123456';
		$onur->repeatPassword = '123456';
		$onur->save();
		
		$seyma = new Person();
		$seyma->isim = 'Şeyma';
		$seyma->soyad = 'Peker';
		$seyma->save();

		$onur->loves($seyma);
		
		//$onur = Person::findById(616);
		$result = $onur->getLoves();
		
		$this->render(['result'=>[$result]]);
	}
	public function merhabaDeAction($isim1,$isim2) {
		echo 'Merhaba'. $isim1.' '.$isim2;
	}
}