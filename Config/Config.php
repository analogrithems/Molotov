<?php
/*
* Movotov Base Settings
*/
return array (
  'esconfig' => 
  array (
    'host' => 'localhost',
    'port' => 9200,
  ),
  'db' => 
  array (
    'driver' => 'Mysql',
    'creds' => 
    array (
      'hostname' => 'localhost',
      'dbname' => 'molotov',
      'username' => 'molotov',
      'password' => 'sdhw93fq2das',
      'charset' => 'utf8',
    ),
    'logging' => 
    array (
      'file' => '/home/analogrithems/development.asynonymous.net/htdocs/Molotov/Logs/sql.query.log',
    ),
  ),
  'logging' => 
  array (
    'file' => '/home/analogrithems/development.asynonymous.net/htdocs/Molotov/Logs/debug',
    'enabled' => true,
    'log_driver' => 'Firephp',
  ),
  'site_url' => 'http://development.asynonymous.net',
  'queue_host' => 'localhost',
  'debug' => false,
  'default_layout' => '/web/default_layout/Common/',
  'test_email' => 'aaron.collinsa@gmail.com',
  'cache' => '/home/analogrithems/development.asynonymous.net/htdocs/Molotov/web/cache',
);
