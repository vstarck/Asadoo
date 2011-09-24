<?php 

/*
 * Este archivo es la entrada para todos los requests del sistema
 * incluyendo assets, js, css e imagenes.
 */

define('BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));
define('BASE_PATH', realpath('../'));

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
		'.'
	);	

    $className = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $className);

    foreach ($paths as $path) {
		$file = BASE_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $className . '.php';
			
        if (file_exists($file)) {
            require_once($file);
            return true;
        }
    }
    return false;
}); 

\lemur\core\Loader::load('lemur/config/config.php');
\lemur\core\Loader::load('lemur/config/constants.php');



// TODO pasar los handlers a un pipeline externo

/*
\lemur\core\Router::getInstance()->addHandler(
	new \lemur\handlers\AssetHandler
);*/
\lemur\core\Router::getInstance()->addHandler(
	new \lemur\handlers\JSHandler
);
\lemur\core\Router::getInstance()->addHandler(
	new \lemur\handlers\BackendHandler
);

\lemur\core\Lemur::start();