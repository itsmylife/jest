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
			if (isset($this->limit[1])) $q .= 'skip '.$this->limit[1]. ' ';
			$q .='limit '.$this->limit[0].' ';
		}
		return $q;
	}
	
	public function findAll($limit=0,$offset=0) {
		$q = $this->build();
		return J::neo()->cypher($q);
	}
	
	public function findAllAs($model, $limit=0, $offset=0) {
		$result = $this->findAll($limit, $offset);
		$nodes = [];
		foreach ($result->data as $data) {
			/** @var NeoNode $neoNode */
			$neoNode = new $model(NeoNode::getIdFromResult($data[0]));
			foreach ($data[0]->data as $param=>$value) {
				$neoNode->params[$param] = $value;
			}	
			$nodes[] = $neoNode;
		}
		return $nodes;
	}
}