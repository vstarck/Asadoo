<?php
/*
 * Este archivo es la entrada para todos los requests del sistema
 * incluyendo assets, js, css e imagenes.
 */

define('BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));
define('BASE_PATH', realpath(__DIR__));

/**
 * Autoloader ciruja
 *
 * Funciona unicamente para las clases de lemur
 *
 * @param string $className
 * @return bool
 */
spl_autoload_register(function ($className) {		
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

\lemur\core\Loader::load('config/config.php');
\lemur\core\Loader::load('config/constants.php');
