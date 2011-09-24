<?php
namespace lemur\core;

class Loader {
	public static function load($filepath) {
		$filepath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $filepath);

		if(substr($filepath, 0, 1) != DIRECTORY_SEPARATOR) {
			$filepath = DIRECTORY_SEPARATOR . $filepath;
		}

		$filepath = BASE_PATH . $filepath;

		if(!file_exists($filepath)) {
			return false;
		}

		require_once($filepath);

		return true;
	}
}