<?php
$rootDir = __DIR__.'/../..';
$appDir  = $rootDir.'/App';
$jestDir = __DIR__.'/../../Jest';

define('Env','Dev');

include($jestDir.'/J.php');
J::init($rootDir,$appDir);