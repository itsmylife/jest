<?php

namespace J;
use J;

class NeoModel extends NeoNode{
	public $labels;
	public $fields;
	public $node;
	
	public function save() {
		$properties = [];
		foreach ($this->fields as $field=>$options) {
			if (isset($this->$field)) $properties[$field] = $this->$field;
		}
		$this->create($this->labels,$properties);
	}
	
	public static function findById($id) {
		return new static($id);
	}
	
	public function query($identifier='n') {
		$q = new NeoQuery();
		$q->addStart($identifier.'=node('.$this->id.')');
		return $q;
	}
}