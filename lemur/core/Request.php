<?php
namespace lemur\core;

class Request {
	private $postVars;
	private $getVars;
	private $cookieVars;
	private $uri;
	
	private static $instance;
	
	public static function create() {	
		if(self::$instance) {
			return self::$instance;
		}
	
		$instance = new self;
		
		$instance->postVars = $_POST;
		$instance->getVars = $_GET;
		$instance->cookieVars = $_COOKIE;
		$instance->uri = isset($_GET['__req']) && $_GET['__req'] ? $_GET['__req'] : '/';

		unset($_POST, $_GET, $_COOKIE, $_REQUEST, $instance->getVars['__req']);		

		return self::$instance = $instance;
	}
	
	private function __construct() {}
	private function __clone() {}	
	
	public function post($key, $fallback = null) {
		return isset($this->postVars[$key]) ? $this->postVars[$key] : $fallback;
	}
	
	public function get($key, $fallback = null) {
		return isset($this->getVars[$key]) ? $this->getVars[$key] : $fallback;
	}
	
	public function cookie($key, $fallback = null) {
		return isset($this->cookieVars[$key]) ? $this->cookieVars[$key] : $fallback;
	}

	public function segment($index = 0) {
		$parts = explode('/', $this->uri);

		return isset($parts[$index]) ? $parts[$index] : null ;
	}

	public function lastSegment() {
		$parts = explode('/', $this->uri);
		
		return end($parts);
	}

	public function uriTail() {
		$parts = explode('/', $this->uri);

		array_shift($parts);

		return $parts;
	}
}