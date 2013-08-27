<?php

namespace Main;
use J;
use J\NeoNode;
use J\Neo;


class Person extends J\NeoModel {
	public $labels = ['Person'];
	
	public $isim;
	public $soyad;

	public $fields = [
		'isim'=>[
			'label'=>'İsim',
			'constraints'=>[
				['type'=>'length','min'=>3,'max'=>5, 'message'=>'blah', 'on'=>'Register'],
				['type'=>'int', 'message'=>'Sayı olmak zorunda'],
			]
		],
		'soyad'=>[
			'label'=>'Soyad'
		]
	];
	
	public function loves($person) {
		$this->relateWith($person,'Loves');
	}
	
	public function getLoves($criteria) {
		$q = $this->query()
			->addMatch('$n-[:Loves]->($m)')
			->addReturn('m')
			->build()
		;
		return $q;
	}
	
}