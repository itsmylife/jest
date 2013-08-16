<?php
require_once(__DIR__.'/JAutoloader.php');
/**
 * This is the main class of Jest Framework
 * It's full static and it is the brain and start point of all classes
 * Class J
 */
class J {
	/** @var array self::$options All configuration options of Jest */
	public static $options = [];

	/**
	 * This will init some required startup components
	 * @param string $appDir Application Directory
	 */
	public static function init($appDir)
	{
		self::configure($appDir);		
		self::autoloader();
		self::app()->init();
	}

	/**
	 * We are merging some initial options with App/Config directory options
	 * Config directory will be scanned for options and after that App/Config/[Env] directory will be scanned
	 * All the results will be merged into J::$options 
	 * @param string $appDir Application Directory
	 */
	private static function configure($appDir)
	{
		self::$options = [
			'dirs'=>[
				'app'=>realpath($appDir),
				'web'=>realpath($appDir.'/Web'),
				'jest'=>__DIR__
			],
			'importPaths'=>[
				'/Modules'
			]
		];
		$confDir = self::getAppDir().'/Conf';
		$confFiles = glob($confDir.'/*.php');
		$envDir = $confDir.'/'.Env;
		if (is_dir($envDir)) {
			$confFiles = array_merge($confFiles, glob($envDir.'/*.php'));
		}
		foreach ($confFiles as $confFile)
		{
			$options = include($confFile);
			self::$options = array_merge($options,self::$options);
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
	 * @return JRouter
	 */
	public static function router() {
		return JRouter::getInstance();
	}

	/**
	 * This returns singleton Autoloader component of J
	 * @return JAutoloader
	 */
	public static function autoloader() {
		return JAutoloader::getInstance();
	}

	/**
	 * This returns singleton App component of J
	 * @return JApp
	 */
	public static function app() {
		return JApp::getInstance();
	}
}