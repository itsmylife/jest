<?php

namespace J;
use J;

class Jade {	
	private $controller;
	private $action;
	private $module;
	
	private $cacheFile;
	private $viewFile;
	
	private $indentType=null;
	
	
	function __construct($module,$controller,$action) {
		$this->action = $action;
		$this->controller = preg_replace('%Controller$%','',$controller);
		$this->module = $module;

		$jadeCacheDir = J::path("App/Cache/Jade/$this->module/$this->controller");
		(is_dir($jadeCacheDir)) || mkdir($jadeCacheDir,0777,true);
		$this->cacheFile = J::path("$jadeCacheDir/$this->action.php");
		$this->viewFile = J::path("App/Modules/$this->module/Views/$this->controller/$this->action.jade");
	}
	
	public function render($params) {			
		if ($this->isUpdated()) {
			$this->parseToCache($params);
		}
		ob_start();
		require($this->cacheFile);
		return ob_get_clean();
	}
	
	private function isUpdated() {
		/*if (is_file($this->cacheFile)) {
			return filemtime($this->cacheFile)<filemtime($this->viewFile);
		}*/
		return true;
	}
	
	private function parseToCache($params) {		
		$content = $this->parse(file_get_contents($this->viewFile),$params);		
		file_put_contents($this->cacheFile,$content);
	}
	
	private function parse($content,$params) {
		$parsed = '';
		$content = explode(PHP_EOL,$content);
		$lastIndent = 0;
		$tree = [];
		foreach ($content as $row) {
			$indent = $this->getIndent($row);
			$rowData = $this->analyzeRow($row);
			if ($docType = $this->checkDocType($row)) {
				$parsed .= $docType.PHP_EOL;
				continue;
			}
		}
		return $parsed;
	}
	
	private function checkDocType($row) {
		$row = preg_replace('/^doctype/','!!!',$row);
		if ($row=='!!!') return '<!DOCTYPE html>';
		if (preg_match('/^!!! (.+)/',$row,$match)) {
			$param = strtolower($match[1]);
			switch ($param) {
				case '5': return '<!DOCTYPE html>';
				case 'xml' : return '<?xml version="1.0" encoding="utf-8" ?>';
				case 'transitional' : return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				case 'strict' : return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				case 'frameset' : return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				case '1.1' : return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				case 'basic' : return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">';
				case 'mobile' : return '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">';
			}
		} 
		return false;		
	}
	
	private function getIndent(&$row) {
		if ($this->indentType===null && preg_match('/^( +|\t)/',$row,$match)) {
			$this->indentType = $match[1];
		}
		if ($this->indentType!==null) {
			if (preg_match('/^('.$this->indentType.'+)(.*)/',$row,$indentMatch)) {
				$row = $indentMatch[2];
				return substr_count($indentMatch[1],$this->indentType);
			}
		}
		return 0;
	}
	
	private function analyzeRow($row) {
		$rowData = [];
		$mainPattern = '/^(((|\.|#|:)((\'|")\|[a-z1-9_$()?:"\'{}=-]+\|(\'|")|[a-z_-]+)+)+)(\((.+)\))*/i';
		if (preg_match($mainPattern, $row, $match)) {
			$objecters = $match[1];
			//implode php parameters
			$html = preg_replace('/^(\.|#|:).+','',$objecters);
			$paramString = $match[2];
			$paramString = str_replace(['\\"',"\\'"],['%%#dq#%%','%%#q#%%'],$paramString);
			$params = [];
			$this->setParamsToRowData('/([a-z._-]+)=\'(.+?)\'/',$paramString);
			$this->setParamsToRowData('/([a-z._-]+)="(.+?)"/',$paramString);
			$paramString = trim($paramString).',';
			$this->setParamsToRowData('/([a-z._-]+)=(.+?),/',$paramString);
			$rowData['html'] = $html;
			$rowData['params'] = $params;
		}		
		return $rowData;
	}
	
	private function setParamsToRowData($pattern,&$paramString) {
		if (preg_match_all($pattern, $paramString, $paramMatch, PREG_SET_ORDER)) {
			$paramString = preg_replace($pattern,'',$paramString);
			foreach ($paramMatch as $pM) {
				$pM[2] = str_replace(['%%#dq#%%','%%#q#%%'],['\\"',"\\'"],$pM[2]);
				$params[] = [$pM[1]=>$pM[2]];
			}
		}
	}
}