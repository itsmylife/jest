<?php

namespace Member;
use J;

class Member extends J\NeoModel {
	public $labels = ['Member'];
	public $username;
	public $email;
	public $password;
	public $repeatPassword;
	public $salt;
	
	public $fields = [
		'username'=>[
			'label'=>'Kullanıcı Adı', 
			'type' =>'textField',
			'constraints'=>[
				['type'=>'required', 'on'=>'signUp'],
				['type'=>'unique', 'on'=>'signUp']
			]
		],
		'email'=>[
			'label'=>'E-Posta',
			'type' => 'textField',
			'constraints'=>[
				['type'=>'required', 'on'=>'signUp'],
				['type'=>'unique', 'on'=>'signUp']
			]
		],
		'password' => [
			'label'=>'Şifre',
			'type'=>'passwordField',
			'constraints'=>[
				['type'=>'required', 'on'=>'signUp'],
			]
		],
		'repeatPassword' => [
			'label' => 'Şifre (Tekrar)',
			'type'=>'passwordField',
			'mapped' => false,
			'constraints'=>[
				['type'=>'required', 'on'=>'signUp'],
				['type'=>'same', 'with'=>'password', 'on'=>'signUp']
			]
		],
		'salt'=>[
			'constraints' => [
				['type'=>'notSafe', 'on'=>'signUp']
			]
		]
	];
}