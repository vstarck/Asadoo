<?php
/*
 * Lemur
 *
 * Copyright (c) 2011 Valentin Starck
 *
 * May be freely distributed under the MIT license. See the MIT-LICENSE file.
 */

define('BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));
define('BASE_PATH', realpath(__DIR__));

/**
 * Autoloader
 * 
 * @param string $className
 * @return bool
 */
spl_autoload_register(function ($className) {
	// TODO move this outside, and add project autoload paths		
    $paths = array(
		BASE_PATH
	);	

    $className = preg_replace('/(\\\)?lemur/', '', $className);
    $className = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $className);

    foreach ($paths as $path) {
		$file = $path . DIRECTORY_SEPARATOR . $className . '.php';
        if (file_exists($file)) {
            require_once($file);
            return true;
        }
    }
    return false;
});

// TODO merge project and lemur configs
\lemur\core\Loader::load('config/config.php');
\lemur\core\Loader::load('config/constants.php');
