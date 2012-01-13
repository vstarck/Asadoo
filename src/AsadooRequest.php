<?php
class AsadooRequest {
	private $variables = array();

	public function segment($index) {}

	public function has($match) {
	    return strpos($this->url(), $match) !== false;
	}

	public function get($key, $fallback = null) {
		if(isset($this->variables[$key])) {
			return $this->variables[$key];
		}

		if(isset($_REQUEST[$key])) {
			return $_REQUEST[$key];
		}

		return $fallback;
	}

	public function set($key, $value = null) {
		if(is_array($key)) {
			foreach($key as $k => $v) {
				$this->set($k, $v);
			}
			return;
		}

		$this->variables[$key] = $value;

		return $value;
	}

	public function isPost() {
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	public function url() {
		$url = $this->domain() . $_SERVER['REQUEST_URI'];

		if(substr($url, -1, 1) == '/') {
			$url = substr($url, 0, -1);
		}

		return $url;
	}

	public function domain() {
		return $_SERVER['SERVER_NAME'];
	}

	public function ip() {}
}