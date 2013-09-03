<?php

use J\App;
use J\Router;
use J\Autoloader;
use J\Purifier;
use J\Neo;
use J\Constraint;

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
	
	//request's routed module:controller:action
	public static $moduleName;
	public static $controllerName;
	public static $actionName;

	//user created module names
	public static $moduleNames;
	
	/**
	 * This will init some required startup components
	 * @param string $appDir Application Directory
	 */
	public static function init($appDir)
	{
		self::$appDir = $appDir;		
		self::configure();
		self::$moduleNames = self::getModuleNames();
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
			'layout'=>'App/Resources/Layouts/main',
			'mainModule'=>'Main',
			'templateEngine'=>'J\Jade'
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

	public static function path($pathAlias) {
		preg_match('%^(.+?)(/.*)%',$pathAlias,$matches);
		$root = $matches[1];
		if (in_array($root,self::$moduleNames)) $root = self::getAppDir().'/Modules/'.$root;
		elseif ($root=='App') $root = self::getAppDir();
		elseif ($root=='Jest') $root = self::getJestDir();
		return $root.$matches[2];
	}

	public static function getModuleNames() {
		$moduleDir = J::getAppDir().'/modules';
		$modulePaths = glob($moduleDir.'/*',GLOB_ONLYDIR|GLOB_NOSORT);
		$modules = [];
		foreach ($modulePaths as $modulePath) $modules[] = pathinfo($modulePath,PATHINFO_BASENAME);
		return $modules;
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

	/**
	 * This returns singleton Purifier component of J
	 * @return Neo
	 */
	public static function neo() {
		return Neo::getInstance();
	}

	/**
	 * This returns singleton Constraint component of J
	 * @return Constraint
	 */
	public static function constraint() {
		return Constraint::getInstance();
	}
	
	/**
	 * @return J\Jade
	 */
	public static function templater() {
		$templaterClass = self::$options['templateEngine'];
		return new $templaterClass(J::$moduleName,J::$controllerName,J::$actionName);
	}
}