<?php

use J\App;
use J\Router;
use J\Autoloader;
use J\Purifier;

require_once(__DIR__.'/Autoloader.php');
/**
 * This is the main class of Jest Framework
 * It's full static and it is the brain and start point of all classes
 * Class J
 */
class J {
	/** @var array self::$options All configuration options of Jest */
	public static $options = [];
	
	/** @var string self::$appDir Initial value of Application Directory*/
	private static $appDir;

	/**
	 * This will init some required startup components
	 * @param string $appDir Application Directory
	 */
	public static function init($appDir)
	{
		self::$appDir = $appDir;
		self::configure();		
		self::autoloader();
		self::app()->init();
	}

	/**
	 * @return array Initial Options of Jest
	 */
	public static function getInitialOptions() {
		return [
			'dirs'=>[
				'app'=>realpath(self::$appDir),
				'web'=>realpath(self::$appDir.'/Web'),
				'jest'=>__DIR__
			],
			'importPaths'=>[
				'{J}/','/Modules/**'
			],
			'mainModule'=>'Main'
		];
	}
	
	/**
	 * We are merging some initial options with App/Config directory options
	 * Config directory will be scanned for options and after that App/Config/[Env] directory will be scanned
	 * All the results will be merged into J::$options 
	 */
	private static function configure()
	{
		self::$options = self::getInitialOptions();
		$confDir = self::getAppDir().'/Conf';
		$confFiles = glob($confDir.'/*.php');
		$envDir = $confDir.'/'.Env;
		if (is_dir($envDir)) {
			$confFiles = array_merge($confFiles, glob($envDir.'/*.php'));
		}
		foreach ($confFiles as $confFile)
		{
			$options = include($confFile);
			self::$options = array_merge(self::$options,$options);
		}
	}

	/**
	 * @return string Jest Directory
	 */
	public static function getJestDir() {
		return self::$options['dirs']['jest'];
	}

	/**
	 * @return string Application Directory
	 */
	public static function getAppDir() {
		return self::$options['dirs']['app'];
	}

	/**
	 * This returns singleton Router component of J
	 * @return Router
	 */
	public static function router() {
		return Router::getInstance();
	}

	/**
	 * This returns singleton Autoloader component of J
	 * @return Autoloader
	 */
	public static function autoloader() {
		return Autoloader::getInstance();
	}

	/**
	 * This returns singleton App component of J
	 * @return App
	 */
	public static function app() {
		return App::getInstance();
	}

	/**
	 * This returns singleton Purifier component of J
	 * @return Purifier
	 */
	public static function purifier() {
		return Purifier::getInstance();
	}
}