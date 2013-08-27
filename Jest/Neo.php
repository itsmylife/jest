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
		$this->address = $this->host.':'.$this->port;
	}

	public static function getInstance() {
		if (self::$instance == null) self::$instance = new self();
		return self::$instance;
	}
	
	public function sendRequest($procedure,$type='POST',$data=[]) {
		$data_string = json_encode($data,JSON_FORCE_OBJECT);

		$ch = curl_init($this->address.'/'.$procedure);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'X-Stream: true'
			)				
		);
		$result = json_decode(explode(PHP_EOL.PHP_EOL,curl_exec($ch))[1]);
		$returnType = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (isset($result->exception)) {
			throw new Exception($returnType.'-'.$result->exception.':'.$result->message);
		} else {
			return $result;
		}
	}		
	
	public function cypher($query,$params=[]) {
		$data = ['query'=>$query,'params'=>$params];
		return $this->sendRequest('db/data/cypher?includeStats=true','POST',$data);
	}
	
	public function arrayToNeoString($array) {
		$str = [];
		foreach ($array as $q=>$v) {
			if (!is_numeric($v)) $v = '"'.$v.'"';
			$str[] = $q.':'.$v;
		}
		return join(',',$str);
	}
	
	public function clearGraph() {
		$q = "START n=node(*) MATCH n-[r?]->() WHERE {endPoint} in labels(n) AND id(n)<>0 DELETE n,r";
		$params['endPoint'] = $this->endPoint;
		return $this->cypher($q,$params);
	}
	
	public function select($query, $params=[]) {
		$data = ['query'=>$query,'params'=>$params];
		return $this->sendRequest('db/data/cypher?includeStats=true','POST',$data);
	}
	
	public function startTransaction() {
		$this->transaction = new NeoTransaction($statements);
	}
}