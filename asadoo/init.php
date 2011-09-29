<?php
/*
 * asadoo
 *
 * Copyright (c) 2011 Valentin Starck
 *
 * May be freely distributed under the MIT license. See the MIT-LICENSE file.
 */

define('BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));
define('BASE_PATH', realpath(__DIR__));

require_once(BASE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'asadoo.php');

/**
 * Autoloader
 * 
 * @param string $className
 * @return bool
 */
spl_autoload_register(function ($className) {	
    $paths = array(
		BASE_PATH
	);

    $asadooName = preg_replace('/(\\\)?asadoo/', '', $className);
    $asadooName = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $asadooName);

    foreach ($paths as $path) {
		$file = $path . DIRECTORY_SEPARATOR . $asadooName . '.php';
        if (file_exists($file)) {
            require_once($file);
            return true;
        }
    }
	
	// Project files
	foreach (\asadoo\core\asadoo::get('project_autoload_paths', array()) as $path) {
		$file = PROJECT_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $className . '.php';
		
        if (file_exists($file)) {
            require_once($file);
            return true;
        }		
	}
	
    return false;
});

// TODO merge project and asadoo configs
\asadoo\core\Loader::load('config/config.php');
\asadoo\core\Loader::load('config/constants.php');
