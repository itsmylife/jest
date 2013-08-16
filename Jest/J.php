<?php
require_once(__DIR__.'/JAutoloader.php');
/**
 * This is the main class of Jest Framework
 * Class J
 */
class J {
	public static $options = [];
	public static function init($rootDir,$appDir)
	{
		self::configure($rootDir,$appDir);		
		self::autoloader();
		self::app()->init();
	}
	
	private static function configure($rootDir,$appDir)
	{
		self::$options = [
			'dirs'=>[
				'app'=>realpath($appDir),
				'web'=>realpath($appDir.'/Web'),
				'root'=>realpath($rootDir),
				'jest'=>__DIR__
			],
			'importPaths'=>[
				'/Jest','/App/Modules'
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
	
	public static function getJestDir() {
		return self::$options['dirs']['jest'];
	}
	
	public static function getRootDir() {
		return self::$options['dirs']['root'];
	}
	
	public static function getAppDir() {
		return self::$options['dirs']['app'];
	}
	
	public static function router() {
		return JRouter::getInstance();
	}
	
	public static function autoloader() {
		return JAutoloader::getInstance();
	}
	
	public static function app() {
		return JApp::getInstance();
	}
}