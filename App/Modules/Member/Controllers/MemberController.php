<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 02.09.2013
 * Time: 13:38
 * To change this template use File | Settings | File Templates.
 */

namespace Member;
use J;
use J\Controller;

class MemberController extends Controller {
	public function signUpAction() {
		$member = new Member();
		$signUpForm = new SignUpForm($member);
		if ($signUpForm->isValid()) {
			echo "Girdim";
		}
		$signUpForm->rowTemplate = J::path('Member/Views/Forms/row.jade');
		$resp['form'] = $signUpForm;
		$this->render($resp);
	}
}