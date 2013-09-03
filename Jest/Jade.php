<?php

namespace J;
use J;

/**
 * This parse a modified jade syntax
 * Class Jade
 * @package J
 */
class Jade {	
	private $controller;
	private $action;
	private $module;
	
	private $cacheFile;
	private $viewFile;
	
	private $indentType=null;
	private $htmlTree = [];
	private $phpTree = [];

	/**
	 * Configurate the paths of cache and view file
	 * @param $module
	 * @param $controller
	 * @param $action
	 */
	function __construct($module,$controller,$action) {
		$this->action = $action;
		$this->controller = preg_replace('%Controller$%','',$controller);
		$this->module = $module;

		$jadeCacheDir = J::path("App/Cache/Jade/$this->module/$this->controller");
		(is_dir($jadeCacheDir)) || mkdir($jadeCacheDir,0777,true);
		$this->cacheFile = J::path("$jadeCacheDir/$this->action.php");
		$this->viewFile = J::path("App/Modules/$this->module/Views/$this->controller/$this->action.jade");
	}

	/**
	 * Renders the view file into cache file and include the cache file
	 * @param $params
	 * @param null $viewFile
	 * @param null $cacheFile
	 * @return string
	 */
	public function render($params,$viewFile=null,$cacheFile=null) {
		extract($params);
		if ($viewFile && $cacheFile==null) {
			$cacheFile = str_replace(J::getAppDir(),J::path('App/Cache/Jade/App'),$viewFile);
			$cacheFile = str_replace('.jade','.php',$cacheFile);
			$cacheDir = pathinfo($cacheFile,PATHINFO_DIRNAME);
			if (!is_dir($cacheDir)) mkdir($cacheDir,0777,true);
		}
		if ($viewFile==null) $viewFile = $this->viewFile;
		if ($cacheFile==null) $cacheFile = $this->cacheFile;
		if ($this->isUpdated($viewFile,$cacheFile)) {
			$this->parseToCache($viewFile,$cacheFile);
		}
		ob_start();
		require($cacheFile);
		return ob_get_clean();
	}

	/**
	 * Renders the view with layout file, if layout file missing, this uses the global layout file
	 * @param $params
	 * @param null $layoutFile
	 * @return string
	 */
	public function renderWithLayout($params,$layoutFile=null) {
		if ($layoutFile==null) $layoutFile = J::$options['layout'];
		$viewFile = J::path($layoutFile).'.jade';
		$fileName = pathinfo($layoutFile,PATHINFO_DIRNAME);
		$cachePath = J::path('App/Cache/Jade/'.$fileName);
		if (!is_dir($cachePath)) mkdir($cachePath,0777,true);
		$cacheFile = $cachePath.'/'.pathinfo($layoutFile,PATHINFO_BASENAME).'.php';		
		$params['actionContent'] = $this->render($params);
		$rendered = $this->render($params,$viewFile,$cacheFile);
		return $rendered;
	}
	/**
	 * Looks that is view file modified after its cached
	 * @param $viewFile
	 * @param $cacheFile
	 * @return bool
	 */
	private function isUpdated($viewFile,$cacheFile) {
		if (file_exists($cacheFile)) {
			$cacheMTime = filemtime($cacheFile);
			$fileMTime = filemtime($viewFile);
			return ($cacheMTime<$fileMTime);
		}
		return true;
	}

	/**
	 * Parses the content into cache file
	 * @param $viewFile
	 * @param $cacheFile
	 */
	private function parseToCache($viewFile,$cacheFile) {
		$content = $this->parse(file_get_contents($viewFile));		
		file_put_contents($cacheFile,$content);
	}

	/**
	 * @param $content
	 * @return string Parsed Content
	 */
	private function parse($content) {
		$parsed = '';
		$content = explode(PHP_EOL,$content);
		$content[] = '';
		$lastIndent = 0;
		$blockedIndent = -1;
		$multilineString = -1;
		$this->tree = [];
		foreach ($content as $row) {
			$this->outPutRow($row,$parsed,$lastIndent,$blockedIndent,$multilineString);
		}
		return $parsed;
	}

	/**
	 * This method process rows one by one and write parsed data to $parsed
	 * @param $row
	 * @param $parsed
	 * @param $lastIndent
	 * @param $blockedIndent
	 * @param $multilineString
	 */
	public function outPutRow($row,&$parsed,&$lastIndent,&$blockedIndent,&$multilineString) {
		$indent = $this->getIndent($row);
		if ($blockedIndent>=0) {
			if ($indent==$blockedIndent) {
				$blockedIndent = -1;
			} else {
				$lastIndent = $indent;
				return;
			}
		}
		if ($multilineString>=0) {
			if ($indent<=$multilineString) {
				$multilineString = -1;
			} else {
				$row = '| '.$row;
			}
		}
		if ($docType = $this->checkDocType($row)) {
			$parsed .= $docType.PHP_EOL;
			$lastIndent = $indent;
			return;
		}
		$rowData = $this->analyzeRow($row);
		if (empty($rowData['tag']) && (!empty($rowData['classes']) || !empty($rowData['id']))) $rowData['tag'] = 'div';
		if (!empty($rowData['parentTag'])) {
			$parsed .= "\n".str_repeat("\t",$indent).'<'.$rowData['parentTag'].'>';
			$this->htmlTree[$indent][] = $rowData['parentTag']; 
			$indent++;
		}
		for ($i=$lastIndent;$i>=$indent;$i--) {
			if (isset($this->htmlTree[$i])) {
				foreach ($this->htmlTree[$i] as $tag) {
					if ($i==$lastIndent && $tag != '!--[if' && preg_match('/<'.$tag.'([^\n]*)>$/si',$parsed)) {
						if ($tag!='!DOCTYPE') $parsed = mb_substr($parsed,0,mb_strlen($parsed)-1).' />';
					} else {
						if ($tag == '!--') $parsed .= "\n".str_repeat("\t",$i).'-->';
						elseif ($tag == '!--[if') $parsed .= "\n".str_repeat("\t",$i).'<![endif]-->';
						elseif ($tag == '?php') $parsed .= "\n".str_repeat("\t",$i).'?>';
						else $parsed .= "\n".str_repeat("\t",$i).'</'.$tag.'>';
					}
				}
				unset($this->htmlTree[$i]);
			}
			if (isset($this->phpTree[$i])) {
				foreach ($this->phpTree[$i] as $p) {
					$parsed .= "\n".str_repeat("\t",$i).'<?php } ?>';
				}
				unset($this->phpTree[$i]);
			}
		}
		if (!empty($rowData['blocked'])) {
			$lastIndent = $indent;
			$blockedIndent = $indent;
		}
		if (!empty($rowData['multiline'])) {
			$multilineString = $indent;
		}
		if (!empty($rowData['tag'])) {
			$this->htmlTree[$indent][] = $rowData['tag'];
			$parsed .= "\n".str_repeat("\t",$indent).'<'.$rowData['tag'];
			if ($rowData['tag']=='!--[if') {
				$parsed .= ' '.$rowData['commentCondition'].']';
			}
			
			
			if (!empty($rowData['id'])) $rowData['params']['id'] = $rowData['id'];
			if (!empty($rowData['classes'])) {
				if (empty($rowData['params']['class'])) $rowData['params']['class'] = '';
				foreach ($rowData['classes'] as $class) {
					$rowData['params']['class'] = trim($rowData['params']['class'].' '.$class);
				}
			}			
			if (!empty($rowData['params'])) {
				foreach ($rowData['params'] as $param=>$value) {
					$aps = is_numeric($value)?'':'"';
					$value = preg_replace('/(\'\|(.+?)\|\'|"\|(.+?)\|")/','<?php echo $2$3; ?>',$value);
					if (!empty($rowData['iEsc'])) {
						foreach ($rowData['iEsc'] as $k=>$v) {
							$value = preg_replace('/_-_-'.$k.'-_-_/','<?php echo '.$v.'; ?>',$value);
						}
					}					
					$parsed .= ' '.$param.'='.$aps.$value.$aps;
				}
			}
			if ($rowData['tag'] == '!DOCTYPE') {
				$parsed .= $rowData['content'];
				$rowData['content'] = null;
			}
			if ($rowData['tag'] != '!--' && $rowData['tag'] != '?php') $parsed .= '>';
		}
		if (!empty($rowData['content'])) {

			$indentTemp = (!empty($rowData['tag']))?str_repeat("\t",$indent+1):str_repeat("\t",$indent);
			$rowData['content'] = preg_replace('/(\'\|(.+?)\|\'|"\|(.+?)\|")/','<?php echo $2$3; ?>',$rowData['content']);
			
			if (preg_match('/^\| *(.+)/',$rowData['content'],$content)) {
				$parsed .= "\n".$indentTemp.str_replace('  ','&nbsp;&nbsp;',$content[1]);
			} elseif (preg_match('/^= *(.+)/',$rowData['content'],$content)) {
				$parsed .="\n".$indentTemp.'<?php echo '.$content[1].'; ?>';
			} elseif (preg_match('/^- *(.+?)(:)*$/',$rowData['content'],$content)) {
				$ending = (!empty($content[2]))?' {':';';
				if ($ending == ' {') {
					$this->phpTree[$indent][] = 1;
				}
				if (preg_match('/<\?php } \?>$/',$parsed) && preg_match('/^ *else/',$content[1])) {
					$parsed = preg_replace('/<\?php } \?>$/','<?php } '.$content[1].$ending.' ?>',$parsed);
				} else {
					$parsed .="\n".$indentTemp.'<?php '.$content[1].$ending.' ?>';
				}				
			} else {
				$parsed .= "\n".$indentTemp.str_replace('  ','&nbsp;&nbsp;',$rowData['content']);
			}
		}
		$lastIndent = $indent;
	}

	/**
	 * returns docType from view
	 * @param $row
	 * @return bool|string
	 */
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

	/**
	 * Return the indent level of row
	 * @param $row
	 * @return int
	 */
	private function getIndent(&$row) {
		if ($this->indentType===null && preg_match('/^( +|\t)/',$row,$match)) {
			$this->indentType = $match[1];
		}
		if ($this->indentType!==null) {
			if (preg_match('/^('.$this->indentType.'+)(.*)/',$row,$indentMatch)) {
				$row = $indentMatch[2];
				$indentCount =  substr_count($indentMatch[1],$this->indentType);
				return $indentCount;
			}
		}
		return 0;
	}

	/**
	 * Analyzes the row for almost everything and collect analyzed data into $rowData
	 * @param $row
	 * @return array
	 */
	private function analyzeRow(&$row) {
		$rowData = [];
		if (!preg_match('/:$/',$row)){
			if (preg_match('/^([a-z-_]+?):/',$row,$parentTag)) {
				$rowData['parentTag'] = $parentTag[1];
			}
		}
		if (preg_match('/^\/if (.+)/',$row,$commentCondition)) {
			$rowData['commentCondition'] = $commentCondition[1];
			$rowData['tag'] = '!--[if';
			return $rowData;
		}
		if (preg_match('/^\/\/\//',$row)) {
			$rowData['blocked'] = true;
			return $rowData;
		}
		if (preg_match('/^\/\//',$row)) {
			$row = preg_replace('/^\/\//','',$row);
			$rowData['tag'] = '!--';
		}
		if (preg_match('/^:php/i',$row)) {
			$rowData['tag'] = '?php';
			if (preg_match('/\.$/',$row)) $rowData['multiline'] = true;
			return $rowData;
		}
		if (preg_match('/^:script/i',$row)) {
			$rowData['tag'] = 'script';
			$rowData['params']['type'] = 'text/javascript';
			if (preg_match('/\.$/',$row)) $rowData['multiline'] = true;
			return $rowData;
		}
		if (preg_match('/^:style/i',$row)) {
			$rowData['tag'] = 'style';
			$rowData['params']['type'] = 'text/css';
			if (preg_match('/\.$/',$row)) $rowData['multiline'] = true;
			return $rowData;
		}
		if (!preg_match('/:$/',$row)){
			$row = preg_replace('/^[a-z-_]+?:/','',$row);
		}
		if (!preg_match('/^-/',$row)) {
			$mainPattern = '/^(((|\.|#)((\'|")\|[a-z1-9_$()?:"\'{}=-]+\|(\'|")|[a-z_-]+)+)+)(\((.+)\))*(\.)*( .+)*/i';
			if (preg_match($mainPattern, $row, $match)) {
				$objecters = $match[1];
				//implode php parameters
				preg_match_all('/(\'\|(.+?)\|\')|("\|(.+?)\|")/',$row,$inlineEscapes,PREG_SET_ORDER);
				foreach ($inlineEscapes as $k=>$iEsc) {
					$rowData['iEsc'][$k] = ($iEsc[2])?$iEsc[2]:$iEsc[4];
					$row = str_replace($iEsc[0],'_-_-'.$k.'-_-_',$row);
					$objecters = str_replace($iEsc[0],'_-_-'.$k.'-_-_',$objecters);
				}
				$tag = preg_replace('/(\.|#|:).+/','',$objecters);
				$objecters .= '#';
				if ($tag=='doctype') $tag = '!DOCTYPE';
				$paramString = isset($match[8])?$match[8]:'';
				$paramString = str_replace(['\\"',"\\'"],['%%#dq#%%','%%#q#%%'],$paramString);
				$params = [];
				$this->setParamsToRowData('/([a-z._-]+)(=\'(.*?)\')*/',$paramString,$params);
				$this->setParamsToRowData('/([a-z._-]+)(="(.*?)")*/',$paramString,$params);
				$paramString = trim($paramString).',';
				$this->setParamsToRowData('/([a-z._-]+)(=(.*?))*,/',$paramString,$params);

				//classes
				preg_match_all('/\.(.+?)[ .#:(]/',$objecters,$classes);
				//id
				preg_match('/#(.+?)[ .#:(]/',$objecters,$id);

				$rowData['tag'] = $tag;
				$rowData['params'] = $params;
				$rowData['classes'] =(!empty($classes[1]))?$classes[1]:[];
				$rowData['id'] = (!empty($id[1]))?$id[1]:'';
				$rowData['content'] = isset($match[10])?$match[10]:null;
				$rowData['multiline'] = isset($match[9])?$match[9]:null;
			} else {
				$rowData['content'] = $row;
			}
		} else $rowData['content'] = $row;		
		if (!empty($rowData['tag']) && $rowData['tag'] == 'doctype') $rowData['tag']='!DOCTYPE';
		return $rowData;
	}

	/**
	 * Set row html attributes to $rowData
	 * @param $pattern
	 * @param $paramString
	 * @param $params
	 */
	private function setParamsToRowData($pattern,&$paramString, &$params) {
		if (preg_match_all($pattern, $paramString, $paramMatch, PREG_SET_ORDER)) {
			$paramString = preg_replace($pattern,'',$paramString);
			foreach ($paramMatch as $pM) {
				if (!isset($pM[3]) && !preg_match('/=/',$paramString)) $pM[3] = $pM[1];
				if (!isset($pM[3])) $pM[3] = '';
				$pM[3] = str_replace(['%%#dq#%%','%%#q#%%'],['\\"',"\\'"],$pM[3]);
				$params[$pM[1]] = $pM[3];
			}
		}
	}
}