<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 27.08.2013
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */

namespace J;
use J;

class NeoQuery {
	public $start = [];
	public $match = [];
	public $where = [];
	public $return = [];
	public $limit = [];
	public $params = [];
	public $returnModel;
	
	public function __construct($returnModel=null) {
		$this->returnModel = $returnModel;
	}
	
	public function init($labels=[]) {
		
	}
	
	public function addStart($start) {
		$this->start[] = $start;
		return $this;
	}
	
	public function addReturn($return) {
		$this->return[] = $return;
		return $this;
	}
	
	public function addMatch($match) {
		$match = preg_replace('/\$([a-z_]+)/i','$1:'.J::neo()->endPoint,$match);
		$match = preg_replace('/\{\$([a-z_]+?)\}/i','$1:'.J::neo()->endPoint,$match);
		$this->match[] = $match;
		return $this;
	}
	
	public function addParameters($paramArray) {
		$this->params = array_merge($this->params,$paramArray);
		return $this;
	}
	
	public function addWhere($where,$type='AND',$depth=0) {
		$this->where[$depth][] = [$type,$where];
		return $this;
	}
	
	public function andWhere($where,$depth=0) {
		$this->addWhere($where,'AND',$depth);
		return $this;
	}
	
	public function orWhere($where,$depth=0) {
		$this->addWhere($where,'OR',$depth);
		return $this;
	}
	
	public function setLimit($limit,$offset=0) {
		$this->limit = [$limit, $offset];
		return $this;
	}
	
	public function build() {
		$q = '';
		if (!empty($this->start)) $q .= 'start '. join(',',$this->start). ' ';
		if (!empty($this->match)) $q .= 'match '. join(',',$this->match). ' ';
		$counter = [];
		$first = true;
		
		if (!empty($this->where)) $q .= 'where ';
		foreach ($this->where as $depth=>$datas) {
			$count = count($this->where[$depth]);
			$counter[$depth] = 0;
			foreach ($datas as $data) {
				if ($first) $data[0] = '';
				if ($counter[$depth] == 0) {
					$q .= ($count>1)?$data[0].'( '.$data[1]: $data[0].' '.$data[1];
				} else {
					$q .= $data[0].' '.$data[1];
				}
				$counter[$depth]++;
				if ($counter[$depth] == $count && $count>1) {
					$q .= ' ) ';
				}
				$q .= ' ';
				$first = false;
			}			
		}
		if (!empty($this->return)) $q .= 'return '. join(',',$this->return). ' ';
		if (!empty($this->limit)) {
			if (!empty($this->limit[1])) $q .= 'skip '.$this->limit[1]. ' ';
			if (!empty($this->limit[0])) $q .='limit '.$this->limit[0].' ';
		}
		return $q;
	}
	
	public function getResult($limit=0,$offset=0) {
		$this->setLimit($limit,$offset);
		$q = $this->build();
		$result = J::neo()->cypher($q,$this->params);
		return (isset($result->data))? $result->data : $result;
	}
	
	public function findAll($model=null, $limit=0, $offset=0) {
		$result = $this->getResult($limit, $offset);
		if (!$model) $model = $this->returnModel;
		if (!$model) throw new Exception('There is no defined return model');
		$nodes = [];
		if (isset($result[0])) {
			foreach ($result[0] as $data) {
				/** @var NeoNode $neoNode */
				$neoNode = new $model(NeoNode::getIdFromResult($data));
				foreach ($data->data as $param=>$value) {
					if (property_exists($neoNode,$param)) $neoNode->{$param} = $value;
					else $neoNode->params[$param] = $value;
				}
				$nodes[] = $neoNode;
			}
		}		
		return $nodes;
	}
	
	public function count() {
		$this->addReturn('count(*)');
		$result = $this->getResult(1);
		return (isset($result[0][0]))?  $result[0][0] : 0;
	}

	public function find($model=null) {
		$result = $this->findAll($model,1);
		return (!empty($result[0]))? $result[0]:null;
	}
}