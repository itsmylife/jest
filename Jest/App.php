<?php

namespace J;
use J;

/**
 * This is our application class
 * It's responsible for the application's actions
 * Class App
 */
class App {
	private static $instance = null;
	function __construct() {
		
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}

	/**
	 * This will init the Application
	 */
	public function init() {
		J::router()->route();
	}
}