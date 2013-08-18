<?php

class JPurifier {
	private static $instance;
	private $forbiddenArgs;
	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	function __construct() {
		//xss forbidden args
		$this->forbiddenArgs = 'onAbortonBlur|onChange|onClick|onDblClick|onDragDrop|onError|onFocus|onKeyDown|';
		$this->forbiddenArgs .= 'onKeyPress|onKeyUp|onLoad|onMouseDown|onMouseMove|onMouseOut|onMouseOver|onMouseUp|';
		$this->forbiddenArgs .= 'onMove|onReset|onResize|onSelect|onSubmit|onUnload';
	}

	/**
	 * Clear the javascript tags from parameter (XSS protection)
	 * @param $parameter
	 * @return mixed
	 */
	public function purify($parameter) {
		$parameter = urldecode($parameter);
		$parameter = preg_replace('%(\n\r|\r|\n)%','',$parameter);
		$parameter = preg_replace('%< *script.*?(/>|</ *script.*?>)%si', '', $parameter);
		$parameter = preg_replace('/('.$this->forbiddenArgs.') *=( *(\'|").*?(\'|")|.+? +)/si', '', $parameter);
		return $parameter;
	}
	
	public function purifyGetAndPostData() {
		foreach ($_GET as $q=>$v) {
			$_GET[$q] = $this->purify($v);
		}
		foreach ($_POST as $q=>$v) {
			$_POST[$q] = $this->purify($v);
		}
	}
}