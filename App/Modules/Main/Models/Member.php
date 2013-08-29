<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 29.08.2013
 * Time: 17:02
 * To change this template use File | Settings | File Templates.
 */

namespace Main;


class Member extends Person {
	public $labels = ['Member','Person'];
	
	public $username;
	public $email;
	public $password;
	public $repeatPassword;
	
	public function __construct() {
		parent::__construct();
		$this->addFields([
				'username'=>[
					'label'=>'Kullanıcı Adı'
				],
				'password'=>[
					'label'=>'Şifre'
				],
				'email'=>[
					'label'=>'E-Posta'
				]
			]);
	}
}