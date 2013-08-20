<?php

namespace J;
use J;

/**
 * This class autoloads everything from importPaths defined in config
 * ['importPaths'=>['/modules','/components']] //looks like this
 * Class JAutoloader
 */
class Autoloader {
	private static $instance = null;
	private $dirs;
	public function __construct()	{
		$this->analyzePaths();
		$this->registerAutoloader();
	}
	
	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function registerAutoloader() {
		spl_autoload_register(function($classname) {
				$classNameParts = explode ('\\',$classname);
				$classname = $classNameParts[count($classNameParts)-1];
			foreach ($this->dirs as $dir)	{
				$file = $dir.'/'.$classname.'.php';
				if (is_file($file)) {
					include_once($file); break;
				}
			}				
		});
	}
	
	public function addSubDirs($path,$depth) {
		$subDirs = glob($path.'/*', GLOB_ONLYDIR|GLOB_NOSORT);
		$depth--;
		foreach ($subDirs as $subDir) {
			$this->dirs[] = $subDir;
			if ($depth != 0) $this->addSubDirs($subDir, $depth);
		}		
	}
	
	public function analyzePaths() {
		foreach (J::$options['importPaths'] as $path) {
			$path = preg_replace('/^\{J\}\//',J::getJestDir().'/',$path);
			$path = preg_replace('/^\//',J::getAppDir().'/',$path);
			$depth = 0;
			do {
				$path = preg_replace('/\/\*$/','',$path,-1,$subDir); 
				if ($subDir) $depth++;
			} while ($subDir);
			$path = preg_replace('/\/\*\*$/','',$path,-1,$recursive);
			if (is_dir($path)) {
				$this->dirs[] = $path;
				if ($recursive) $depth = -1;
				if ($depth != 0) $this->addSubDirs($path, $depth);
			} else {
				throw new Exception('Cannot find the path for autoloading:'.$path);
			}						
		}
	}
	
	
}