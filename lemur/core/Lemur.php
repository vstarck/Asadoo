<?php
namespace lemur\core;

final class Lemur {
	public static function loadTwig() {
		$path = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor'. DIRECTORY_SEPARATOR . 'twig'. 
				DIRECTORY_SEPARATOR . 'lib'. DIRECTORY_SEPARATOR . 'Twig'. 
				DIRECTORY_SEPARATOR . 'Autoloader.php';

		require_once $path;

		\Twig_Autoloader::register();
	}
	
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