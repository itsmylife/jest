<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cm
 * Date: 27.08.2013
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */

namespace J;


class NeoQuery {
	public $start = [];
	public $match = [];
	public $where = [];
	public $return = [];
	
	public function addStart($start) {
		$this->start[] = $start;
		return $this;
	}
	
	public function addReturn($return) {
		$this->return[] = $return;
		return $this;
	}
	
	public function addMatch($match) {
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
	
	public function build() {
		$q = '';
		$q .= join(',',$this->start);
		$q .= join(',',$this->match);
		$counter = [];
		$first = true;
		foreach ($this->where as $depth=>$datas) {
			$count = count($this->where[$depth]);
			$counter[$depth] = 0;
			foreach ($datas as $data) {
				if ($first) $data[0] = '';
				if ($counter[$depth] == 0) {
					$q .= ($count>1)?$data[0].' ( '.$data[1]: $data[0].' '.$data[1];
				} else {
					$q .= $data[0].' '.$data[1];
				}
				$counter[$depth]++;
				if ($counter[$depth] == $count) {
					$q .= ')';
				}
				$q .= ' ';
				$first = false;
			}			
		}
		$q .= join(',',$this->return);
		return $q;
	}
}