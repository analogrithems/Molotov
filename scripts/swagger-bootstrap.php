<?php
print getcwd()."\n\n";
include_once('./bootstrap.php');
	
$di = \Phalcon\DI::getDefault();
define('BASE_PATH', $di->get('config')->site_url.'api/');
error_reporting(E_ERROR | E_WARNING | E_PARSE);