<?php 
// Load config.ini
if(!file_exists('config.ini')) {
	echo 'Invalid config file';
	die();
}

$config = parse_ini_file('config.ini');

if(!isset($config['lemur_path'])) {
	echo 'Invalid config file';
	die();	
}

define('PROJECT_PATH', dirname(__FILE__));

require_once($config['lemur_path'] . DIRECTORY_SEPARATOR . 'init.php');


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

\lemur\core\Lemur::start($config);

unset($config);
