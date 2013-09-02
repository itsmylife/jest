<?php

namespace Member;
use J;
use J\Form;

class SignUpForm extends Form {
	public $name = 'signUp';
	public $model;
	
	function __construct(Member $member) {
		$this->model = $member;
	}
	
	
}