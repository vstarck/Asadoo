<?php
namespace asadoo\core;

final class Asadoo {
	public static function getRouter() {
		return Router::getInstance();
	}

	public static $config = array();
	
	public static function setConfig($config) {
		if(is_array($config)) {
			self::$config = $config;		
		}
	}
	
	public static function start() {
		self::getRouter()->handle();
	}
	
	public static function get($key, $fallback = null) {
		return isset(self::$config[$key]) ? self::$config[$key] : $fallback;
	}
}