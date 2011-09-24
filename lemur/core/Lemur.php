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

	public static function getPropel() {}
	
	public static function getRouter() {
		return Router::getInstance();
	}

	private static $config = array();
	
	public static function start($config = null) {
		if(is_array($config)) {
			self::$config = $config;		
		}		
		
		self::getRouter()->handle();
	}
	
	public static function get($key, $fallback = null) {
		$config = self::$config;
		return isset($config['key']) ? $config['key'] : $fallback;
	}
}