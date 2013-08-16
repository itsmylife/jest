<?php
/**
 * This class autoloads everything from importPaths defined in config
 * ['importPaths'=>['/modules','/components']] //looks like this
 * Class JAutoloader
 */
class JAutoloader {
	private static $instance = null;
	public function __construct()
	{
		//autoload Jest Dir
		$this->registerDir(J::getJestDir());
		//autoload dirs in config
		$dirs = J::$options['importPaths'];
		foreach ($dirs as $dir) $this->registerDir(J::getAppDir().$dir);
	}
	
	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function registerDir($dir)
	{
		if (is_dir($dir)) {
			spl_autoload_register(function($classname) use($dir) {
				$file = $dir.'/'.$classname.'.php';
				if (is_file($file)) include_once($file);
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