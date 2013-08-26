<?php

namespace J;
use J;

class Neo {
	private static $instance;
	private $host;
	private $port;
	public $endPoint;
	private $transaction;
	function __construct() {
		$this->host = J::$options['neo']['host'];
		$this->port = J::$options['neo']['port'];
		$this->endPoint = J::$options['neo']['endPoint'];
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function sendRequest($procedure,$type='POST',$data=[]) {
		$data_string = json_encode($data,JSON_FORCE_OBJECT);

		$ch = curl_init($this->host.':'.$this->port.'/'.$procedure);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'X-Stream: true'
			)				
		);

		return json_decode(curl_exec($ch));
	}		
	
	public function create($query, $params) {
		return $this->sendRequest('db/data/node/0','GET');
	}
	
	public function arrayToNeoString($array) {
		$str = '';
		foreach ($array as $q=>$v) {
			if (!is_numeric($v)) $v = '"'.$v.'"';
			$str .= $q.':'.$v;
		}
		return $str;
	}
	
	public function select($query, $params=[]) {
		$data = ['query'=>$query,'params'=>$params];
		return $this->sendRequest('db/data/cypher?includeStats=true','POST',$data);
	}
	
	public function startTransaction() {
		$this->transaction = new NeoTransaction($statements);
	}
}