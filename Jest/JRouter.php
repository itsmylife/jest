<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 16.08.2013
 * Time: 22:10
 * To change this template use File | Settings | File Templates.
 */

class JRouter {
	private static $instance = null;
	public $url;
	public $uri;
	function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
		//print_r($_SERVER);
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}	
}