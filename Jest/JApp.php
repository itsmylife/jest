<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 16.08.2013
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

class JApp {
	private static $instance = null;
	function __construct() {
		
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function init() {
		echo J::router()->uri;
	}
}