<?php
/**
 * This class is resolving friendly url's to application actions 
 * Class JRouter
 */
class JRouter {
	private static $instance = null;
	public $uri;
	public $host;
	private $mainControllerName;
	public $forbiddenArgs;
	function __construct() {
		$this->uri = preg_replace('/^\//','',$_SERVER['REQUEST_URI']);
		$this->host = $_SERVER['SERVER_NAME'];
		$this->mainControllerName = J::$options['mainModule'].'Controller';
		//xss forbidden args
		$this->forbiddenArgs = 'onAbortonBlur|onChange|onClick|onDblClick|onDragDrop|onError|onFocus|onKeyDown|';
		$this->forbiddenArgs .= 'onKeyPress|onKeyUp|onLoad|onMouseDown|onMouseMove|onMouseOut|onMouseOver|onMouseUp|';
		$this->forbiddenArgs .= 'onMove|onReset|onResize|onSelect|onSubmit|onUnload';
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}

	/**
	 * You can change mainModule option from config but 
	 * Controller name of Main module must be same as the module's name
	 * @return array MainController's Actions
	 */
	private function getMainActions()
	{
		return $this->getControllersActions($this->mainControllerName);
	}

	/**
	 * Returns given controller's actions
	 * @param string $controllerName of which we need action names
	 * @return array given controller's actions
	 */
	private function getControllersActions($controllerName)
	{
		$controller = new ReflectionClass($controllerName);
		$methods = $controller->getMethods();
		$actions = [];
		foreach ($methods as $method) {
			$match = false;
			$methodName = preg_replace('/Action$/','',$method->getName(),-1,$match);
			if ($match) $actions[] = $methodName;
		}
		return $actions;
	}
	
	public function purifyParameter($parameter) {
		$parameter = urldecode($parameter);
		$parameter = preg_replace('%(\n\r|\r|\n)%','',$parameter);
		$parameter = preg_replace('%< *script.*?(/>|</ *script.*?>)%si', '', $parameter);
		$parameter = preg_replace('/('.$this->forbiddenArgs.') *=( *(\'|").*?(\'|")|.+? +)/si', '', $parameter);
		return $parameter;
	}
	
	private function buildParameters($route,$startIndex) {
		$params = [];
		for ($i=$startIndex;$i<count($route);$i++) {
			$params[] = $this->purifyParameter($route[$i]);
		}
		return $params;
	}
	
	public function route()	{		
		$route = explode('/',$this->uri);
		$mainActions = $this->getMainActions();
		//is first part of route in main controllers actions
		if (in_array($route[0],$mainActions)) {
			$this->routeToAction($route[0],$this->buildParameters($route,1));
		}
	}
	
	public function routeToAction($action='index',$params=[],$controller=null)
	{
		if (!$controller) $controller = $this->mainControllerName;
		call_user_func_array([new $controller(),$action.'Action'],$params);
	}
}