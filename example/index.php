<?php 
/*
 * Lemur
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

if(!isset($config['lemur_path'])) {
	echo 'Invalid config file';
	die();	
}

define('PROJECT_PATH', dirname(__FILE__));

require_once($config['lemur_path'] . DIRECTORY_SEPARATOR . 'init.php');

\lemur\core\Lemur::setConfig($config);

// TODO move handlers to an external pipeline
\lemur\core\Router::getInstance()->addHandler(
	// JS path
	new \lemur\handlers\GenericJSHandler(PROJECT_PATH . DIRECTORY_SEPARATOR . 'js'),
	// CSS path
	new \lemur\handlers\GenericCSSHandler(PROJECT_PATH . DIRECTORY_SEPARATOR . 'css'),
	// TODO rename to Generic*Handler
	new CatchAllHandler
);

\lemur\core\Lemur::start();

// Clean up
//unset($config);