<?php
/**
 * Configuration file for \Lh\System\Web\Application. This file can alter how our web application behave.
 * Currently supported key:
 *  - 'phpSettings'				=> key value pair containing php ini setting key and their value for changing PHP ini before any script execution.
 *  - 'application'				=> contains options for your application. Currently only web application supported. @see Application::__construct()
 *  - 'loaderManager'			=> contains options for LoaderManager. @see LoaderManager::_init()
 *  - 'dbManager'				=> contains options for DbManager. @see DbManager;:_init()
 *  - 'errorManager'			=> contains options for ErrorManager. @see ErrorManager::_init()
 *  - 'sessionManager'			=> contains options for SessionManager. @see SessionManager::_init()
 *  - 'authenticationManager'	=> contains options for AuthenticationManager. @see AuthenticationManager::_init()
 *  - 'router'					=> contains options for Router. @see Router::init()
 */

return array(
	'phpSettings' => null,
	'application' => array(
		'name' => 'LH Framework Second Edition',
		'mainScript' => 'index.php',					// Default: 'index.php'. Give 'auto' for auto detection
		'appendScript' => false,						// Default: false
//		'environment' => APPLICATION_ENV,				// Default: APPLICATION_ENV
//		'sourcePath' => APPLICATION_PATH . "src/",		// Default: APPLICATION_PATH . "src/"
//		'bootstrap' => array("class" => "Bootstrap", "file" => "Bootstrap.php")
	),
	'loaderManager' => array(
		'override' => 'DENY',
		'loaders' => array()
	),
	'dbManager' => array(
		'override' => 'DENY',
		'adapters' => array()
	),
	'errorManager' => array(),
	'sessionManager' => array(),
	'authenticationManager' => array()
);

// End of File: application.config.php
 