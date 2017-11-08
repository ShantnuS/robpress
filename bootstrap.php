<?php

//Set up logging
ini_set("log_errors", 1);
ini_set("error_log", "error.log");

//Set up session
session_save_path(getcwd() . DIRECTORY_SEPARATOR . 'tmp');

//Check for database
if(!$f3->get('db')) {
	die('Unable to read database configuration. Ensure your database configuration exists and is correct');
}

//Check for settings 
$settings = Settings::getSettings();
if($settings['debug'] == 1) {

	//Define DEBUG mode as 1 if debug mode is enabled
	define('DEBUG',1);
}
