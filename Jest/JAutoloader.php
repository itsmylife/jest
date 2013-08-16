<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 16.08.2013
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

class JAutoloader {
	private static $instance = null;
	public function __construct()
	{
		$dirs = J::$options['importPaths'];
		foreach ($dirs as $dir) $this->registerDir(J::getRootDir().'/'.$dir);
	}
	
	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function registerDir($dir)
	{
		if (is_dir($dir)) {
			spl_autoload_register(function($classname) use($dir) {
				include_once($dir.'/'.$classname.'.php');
			});
			$this->registerSubDirs($dir);
		} else {
			throw new Exception('This is not a valid directory for import : '.$dir);
		}
	}
	
	public function registerSubDirs($dir)
	{
		$subdirs = glob($dir.'/*',GLOB_ONLYDIR|GLOB_NOSORT);
		foreach ($subdirs as $subdir) $this->registerDir($subdir);
	}
}