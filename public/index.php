<?php
/**
 * Main bootstrap file for LH Framework. This file will be called in every request because of rewrite module.
 * Global variable which used by the whole system will be used here.
 */

// Determine our web application environment. By Default our application should be run in production mode
// We can change this value from server environment variable which accessible when we in development server
if (!defined("APPLICATION_ENV")) {
	define("APPLICATION_ENV", getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production");
}

// Set global variable for our application path here
if (!defined("APPLICATION_PATH")) {
	define("APPLICATION_PATH", realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR);
}

// Set global variable for vendor folder path here
if (!defined("VENDOR_PATH")) {
	define("VENDOR_PATH", APPLICATION_PATH . "vendor/");
}

// Ensure 'vendor/' is on include_path (this is where we will put out dependency / libraries)
set_include_path(implode(PATH_SEPARATOR, array(
	VENDOR_PATH,
	get_include_path()
)));

// Start our web application
require_once(VENDOR_PATH . "Lh/Web/Application.php");
\Lh\Web\Application::init(include(APPLICATION_PATH . "config/system/application.config.php"))
	->start(include(APPLICATION_PATH . "config/user/application.config.php"));