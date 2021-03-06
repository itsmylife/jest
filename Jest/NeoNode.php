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
		if (!isset($this->params[$param])) {
			$nodeData = J::neo()->sendRequest('db/data/node/'.$this->id,'GET');
			if ($nodeData===null) throw new NeoException('Property cannot be found: '.$param);
			foreach($nodeData->data as $paramTemp=>$value) $this->params[$paramTemp] = $value;
		}
		return $this->params[$param];
	}
	
	public function clearPool() {
		$this->params = [];
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
		$params = [
			'props'=>$properties
		];
		$q = "create (n:".join(':',$labels)." { props }) return id(n)";
		$result = J::neo()->cypher($q,$params);
		$this->id = $result->data[0][0];
	}
	
	public function update($properties=[]) {
		$params = [
			'props'=>$properties	
		];
		$q = "START n=node(".$this->id.") SET n={ props }";
		$result = J::neo()->cypher($q,$params);
		return $result->stats->properties_set;
	}
	
	public function getRelations($to=null,$type=null,$data=null) {
		$params = new \stdClass();
		if ($to != null) $params->to = $to;
		if ($type != null) $params->type = $type;
		if ($data != null) $params->data = $data;
		J::neo()->sendRequest('db/data/node/'.$this->id.'/relationships/all','GET');	
	}
	
	public static function getIdFromResult($neoNode) {
		return array_reverse(explode('/',$neoNode->self))[0];
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