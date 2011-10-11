<?php 
/*
 * Asadoo
 *
 * Copyright (c) 2011 Valentin Starck
 *
 * May be freely distributed under the MIT license. See the MIT-LICENSE file.
 */

// Load config.ini
if(!file_exists('config.ini')) {
	echo 'Invalid config file';
	die();
}

$config = parse_ini_file('config.ini');

if(!isset($config['asadoo_path'])) {
	echo 'Invalid config file';
	die();	
}

define('PROJECT_PATH', dirname(__FILE__));

require_once($config['asadoo_path'] . DIRECTORY_SEPARATOR . 'init.php');

$Asadoo = \asadoo\core\asadoo::getInstance();

$Asadoo->setConfig($config);

// Clean up
unset($config);

//---------------------------------------------------------------------------------------------------------------------

// TODO move handlers to an external pipeline
$Asadoo->addHandler(
    // Lambdas
    function(\asadoo\core\Request $request, \asadoo\core\Response $response) {
        if($request->any('lambda')) {
            echo 'Hello Lambda!';
            return false;
        }
    },
	// JS path
	new \asadoo\handlers\GenericJSHandler(PROJECT_PATH . DIRECTORY_SEPARATOR . 'js'),
	// CSS path
	new \asadoo\handlers\GenericCSSHandler(PROJECT_PATH . DIRECTORY_SEPARATOR . 'css'),
	new DocumentationHandler,
	new CatchAllHandler
);

$Asadoo->start();

