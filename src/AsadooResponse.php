<?php
class AsadooResponse {
	// header...
	public function __call($name, $arguments) {}

	public function sendResponseCode() {}
	public function setCache() {}
	public function setNoCache() {}

	public function header($key, $value) {}

	public function send($content) {
		echo $content;
	}

	public function close() {}
}