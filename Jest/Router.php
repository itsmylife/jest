<?php

namespace J;
use J;

/**
 * This class is resolving friendly url's to application actions
 * Class JRouter
 */
class Router
{
	private static $instance = null;
	public $uri;
	public $host;
	private $mainControllerName;

	function __construct() {
		$this->uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);
		$this->host = $_SERVER['SERVER_NAME'];
		$this->mainControllerName = J::$options['mainModule'] . 'Controller';
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}

	/**
	 * Returns given controller's actions
	 * @param string $controllerName of which we need action names
	 * @return array given controller's actions
	 */
	private function getControllersActions($controllerName,$moduleName='Main') {
		$controllerName = Helper::camelize($controllerName,true);
		$moduleName = Helper::camelize($moduleName,true);
		$controller = new \ReflectionClass($moduleName.'\\'.$controllerName . 'Controller');
		$methods = $controller->getMethods();
		$actions = [];
		foreach ($methods as $method) {
			$match = false;
			$methodName = preg_replace('/Action$/', '', $method->getName(), -1, $match);
			if ($match) $actions[] = $methodName;
		}
		return $actions;
	}

	/**
	 * Provides purified parameters for action
	 * @param $route
	 * @param $startIndex
	 * @return array
	 */
	private function buildParameters($route, $startIndex)	{
		$params = [];
		for ($i = $startIndex; $i < count($route); $i++) {
			$params[] = J::purifier()->purify($route[$i]);
		}
		return $params;
	}

	private function getModules() {
		$moduleDir = J::getAppDir().'/modules';
		$modulePaths = glob($moduleDir.'/*',GLOB_ONLYDIR|GLOB_NOSORT);
		$modules = [];
		foreach ($modulePaths as $modulePath) $modules[] = pathinfo($modulePath,PATHINFO_BASENAME);
		return $modules;
	}
	
	private function getModulesControllers($module) {
		$mainControllersPath = J::getAppDir() . '/Modules/' . $module . '/Controllers';
		$controllerFiles = glob($mainControllersPath . '/*Controller.php');
		$controllers = [];
		foreach ($controllerFiles as $controllerFile) {
			$fileName = pathinfo($controllerFile, PATHINFO_FILENAME);
			$controllers[] = preg_replace('/^(.+)Controller$/', '$1', $fileName);
		}
		return $controllers;
	}
	
	private function isInActions($action, $controller, $module=null) {
		if ($module == null) $module = J::$options['mainModule'];
		$action = Helper::camelize($action);
		return in_array($action, $this->getControllersActions($controller, $module));
	}
	
	private function isInControllers($controller, $module) {
		$controller =  Helper::camelize($controller,true);
		$module =  Helper::camelize($module,true);
		return in_array($controller, $this->getModulesControllers($module));
	}
	
	private function isModule($module) {
		$module = Helper::camelize($module,true);
		return in_array($module, $this->getModules());
	}
	
	/**
	 *
	 */
	public function route()	{
		$route = explode('/', $this->uri);
		if (!isset($route[1])) $route[1]='';
		if (!isset($route[2])) $route[2]='';
		//is first part of route in main controllers actions
		if ($this->isInActions($route[0],J::$options['mainModule'],J::$options['mainModule'])) {
			$this->routeToAction($route[0], $this->buildParameters($route, 1));
		} elseif (
			//is first part one of the main module's controllers 
			$this->isInControllers($route[0],J::$options['mainModule']) &&
			//is second part of route in this controllers actions
			$this->isInActions($route[1],$route[0],J::$options['mainModule'])
		) {
			$this->routeToAction($route[1], $this->buildParameters($route, 2), $route[0]);
		} elseif (
			//is first part a module name
			$this->isModule($route[0]) &&
			//is second part is controller of that module
			$this->isInControllers($route[1],$route[0]) &&
			//is third part is action of that controller
			$this->isInActions($route[2],$route[1],$route[0])
		) {
			$this->routeToAction($route[2], $this->buildParameters($route, 3), $route[1], $route[0]);
		}  elseif (
			//is first part a module name
			$this->isModule($route[0]) &&
			//is second part is controller of that module
			$this->isInControllers($route[1],$route[0]) 
		) {
			$this->routeToAction('index', $this->buildParameters($route, 2), $route[1], $route[0]);
		}elseif ($this->isModule($route[0])) {
			$this->routeToAction('index', $this->buildParameters($route, 1), $route[0], $route[0]);
		} else {
			$this->routeToAction('index', $this->buildParameters($route, 0));
		}
	}

	public function routeToAction($action = 'index', $params = [], $controller = null, $module=null) {
		if (!$controller) $controller = J::$options['mainModule'];
		if (!$module) $module = J::$options['mainModule'];
		$action = Helper::camelize($action); 		
		$controller =  Helper::camelize($controller,true).'Controller';
		$module = Helper::camelize($module,true);
		$controllerClass = '\\'.$module.'\\'.$controller;
		J::purifier()->purifyGetAndPostData();
		call_user_func_array([new $controllerClass(), $action . 'Action'], $params);
	}
}