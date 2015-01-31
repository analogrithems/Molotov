<?php
/*
* Movotov Base Settings
*/
return array(
	'esconfig'=> array(
		'host'=> 'localhost',
		'port'=> 9200
	),
	'db'=>array(
	    "hostname" => 'localhost',
	    "username" => "molotov",
	    "password" => "sdhw93fq2das",
	    "dbname" => 'molotov',
	    'logging'=> array(
	    	'file'=>  APP_ROOT_DIR . '/Logs/sql.query.log'
	    )
	),
	'logging'=>array(
		'file'=> APP_ROOT_DIR . '/Logs/debug',
		'enabled'=> true,
		'log_driver'=>'Firephp'
	),
	'site_url'=> 'http://development.asynonymous.net',
	'queue_host'=> 'localhost',
	'debug'=>false,
	'default_layout'=> '/web/default_layout/Common/',
	'test_email'=>'aaron.collinsa@gmail.com',
	'cache'=>APP_ROOT_DIR.'/web/cache'
);
