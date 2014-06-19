<?php
/*
* Elastic Search Server Settings
*/
return array(
	'namespaces' => array(
		'Molotov\Core\Models' =>  APP_ROOT_DIR . "/Core/Models",
		'Molotov\Core\Controllers' =>  APP_ROOT_DIR . "/Core/Controllers",
		'Molotov\Core\Lib' =>  APP_ROOT_DIR . "/Core/Lib",
		'Molotov\Core\Tests' =>  APP_ROOT_DIR . "/Core/Tests",
	),
	'esconfig'=> array(
		'host'=> 'localhost',
		'port'=> 9200
	),
	'db'=>array(
	    "host" => 'localhost',
	    "username" => "molotov",
	    "password" => "sdhw93fq2das",
	    "dbname" => 'molotov',
	    'logging'=> array(
	    	'file'=>  APP_ROOT_DIR . '/Logs/sql.query.log'
	    )
	),
	'logging'=>array(
		'file'=> APP_ROOT_DIR . '/Logs/debug',
		'enabled'=> true
	),
	'site_url'=>"https://vault.asynonymous.net",
	'queue_host'=>'localhost'
	
);
