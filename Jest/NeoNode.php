<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 26.08.2013
 * Time: 13:31
 * To change this template use File | Settings | File Templates.
 */

namespace J;
use J;

class NeoNode {
	public $id;
	public $params;
	function __construct($nodeId=null) {
		if ($nodeId != null) $this->id = $nodeId;
	}
	
	function __get($param) {
		if (isset($this->params[$param])) {
			return $this->params[$param];
		} else {
			$nodeData = J::neo()->sendRequest('db/data/node/'.$this->id,'GET');
			return $this->params[$param] = $nodeData->data->{$param};
		}
	}
	
	public function delete() {
		J::neo()->sendRequest('db/data/node/'.$this->id,'DELETE');		
		return true;
	}
	
	public static function getWithId($id) {
		return new self($id);
	}
	
	public function create($labels=[],$properties=[]) {
		if (is_string($labels)) $labels = [$labels];
		$labels[] = J::neo()->endPoint;
		$properties = J::neo()->arrayToNeoString($properties);
		$q = "create (n:".join(':',$labels)." { ".$properties." }) return id(n)";
		$result = J::neo()->cypher($q);
		$this->id = $result->data[0][0];
	}
	
	public function getRelations($to=null,$type=null,$data=null) {
		$params = new \stdClass();
		if ($to != null) $params->to = $to;
		if ($type != null) $params->type = $type;
		if ($data != null) $params->data = $data;
		J::neo()->sendRequest('db/data/node/'.$this->id.'/relationships/all','GET');
		
	}

	public function getRelatedWith() {
		
	}
	
	public function relateWith($to=null,$type=null,$data=[]) {
		if ($to instanceof NeoNode) $to = $to->id;
		$params = new \stdClass();
		if ($to != null) $params->to = J::neo()->address.'/db/data/node/'.$to;
		if ($type != null) $params->type = $type;
		if ($data != null) $params->data = $data;
		J::neo()->sendRequest('db/data/node/'.$this->id.'/relationships','POST', $params);
	}
}