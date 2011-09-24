<?php
namespace lemur\core;

class Lemur {
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

	public static function start() {
		self::getRouter()->handle();
	}
}