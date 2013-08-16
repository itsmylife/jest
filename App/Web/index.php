<?php
//We defined application and jest directories
$appDir  = __DIR__.'/../../App';
$jestDir = __DIR__.'/../../Jest';

//We defined a constant for out Environment
//This can be 'Dev', 'Test' and 'Prod'
define('Env','Dev');

//We are including our base class
include($jestDir.'/J.php');
J::init($appDir);