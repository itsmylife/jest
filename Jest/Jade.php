<?php

namespace J;
use J;

class Jade {
	private static $instance = null;
	function __construct() {

	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function render($path,$viewName,$params) {
		$cacheFile = J::path('App/Cache/Jade/'.$viewName.'.php');
		$viewFile = J::path($path.'/'.$viewName.'.jade');
		if ($this->isUpdated($cacheFile,$viewFile)) {
			$this->parseToCache($viewFile,$cacheFile,$params);
		}
		ob_start();
		require($cacheFile);
		return ob_get_clean();
	}
	
	private function isUpdated($cacheFile,$viewFile) {
		/*if (is_file($cacheFile)) {
			return filemtime($cacheFile)<filemtime($viewFile);
		}*/
		return true;
	}
	
	private function parseToCache($viewFile,$cacheFile,$params) {
		$jadeDir = J::path('App/Cache/Jade/');
		(is_dir($jadeDir)) || mkdir($jadeDir,0777,true);
		$content = file_get_contents($viewFile);
		$content = '<?php echo "Merhaba Cache";';
		file_put_contents($cacheFile,$content);
	}
}