<?php

namespace Main;

use J\Controller;

class MainController extends Controller {
	public function indexAction($isim) {
		$this->render(['isim'=>$isim]);
	}
	public function merhabaDeAction($isim1,$isim2) {
		echo 'Merhaba'. $isim1.' '.$isim2;
	}
}