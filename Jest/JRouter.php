<?php
/**
 * This class is resolving friendly url's to application actions 
 * Class JRouter
 */
class JRouter {
	private static $instance = null;
	public $url;
	public $uri;
	function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
		print_r($_SERVER);
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}	
}