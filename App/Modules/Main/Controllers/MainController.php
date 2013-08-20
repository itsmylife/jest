<?php

namespace Main;

class MainController {
	public function indexAction($isim)
	{
		echo 'Merhaba '.$isim;
	}
	public function merhabaDeAction($isim1,$isim2)
	{
		echo 'Merhaba'. $isim1.' '.$isim2;
	}
}