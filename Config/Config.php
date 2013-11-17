<?php
/*
* Elastic Search Server Settings
*/
return array(
	'namespaces' => array(
		'Molotov\Core\Models' =>  APP_ROOT_DIR . "/Core/Models",
		'Molotov\Core\Controllers' =>  APP_ROOT_DIR . "/Core/Controllers",
		'Molotov\Core\Tests' =>  APP_ROOT_DIR . "/Core/Tests",
	),
	'esconfig'=> array(
		'host'=> 'localhost',
		'port'=> 9200
	),
	'db'=>array(
	    "host" => 'localhost',
	    "username" => "av",
	    "password" => "sdhw93fq2das",
	    "dbname" => 'av',
            "table_prefix" => 'av_',
	    'logging'=> array(
	    	'file'=>  APP_ROOT_DIR . '/Logs/sql.query.log'
	    )
	),
	'logging'=>array(
		'file'=> APP_ROOT_DIR . '/Logs/debug',
		'enabled'=> true
	),
	'site_url'=>"https://vault.asynonymous.net"
	
);
