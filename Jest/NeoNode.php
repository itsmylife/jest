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
	function __construct($nodeId=null) {
		if ($nodeId != null) $this->id = $nodeId;
	}
	
	public function delete() {
		$result = J::neo()->sendRequest('db/data/node/'.$this->id,'DELETE');
		if (isset($result->exception)) {
			throw new Exception($result->exception.':'.$result->message);
		}
		return true;
	}
	
	public static function getWithId($id) {
		$nodeData = new \stdClass();
		$nodeData->id = $id;
		return new self($nodeData);
	}
	
	public static function create($name, $properties=[]) {
		$properties['name'] = $name;
		$properties = J::neo()->arrayToNeoString($properties);
		$endPoint = J::neo()->endPoint;
		$q = "create (n:{$endPoint} { $properties }) return n";
		$data = ['query'=>$q];
		$result = J::neo()->sendRequest('db/data/cypher','POST',$data);
		$id = array_reverse(explode('/',$result->data[0][0]->self))[0];
		return new self($id);
	}
	
	public function getRelationships($to=null,$type=null,$data=null) {
		$params = new \stdClass();
		if ($to != null) $params->to = $to;
		if ($type != null) $params->type = $type;
		if ($data != null) $params->data = $data;
		J::neo()->sendRequest('db/data/node/'.$this->id.'/relationships','POST');
	}

	public function createRelationships($to=null,$type=null,$data=null) {
		$params = new \stdClass();
		if ($to != null) $params->to = $to;
		if ($type != null) $params->type = $type;
		if ($data != null) $params->data = $data;
		J::neo()->sendRequest('db/data/node/'.$this->id.'/relationships','POST');
	}
}