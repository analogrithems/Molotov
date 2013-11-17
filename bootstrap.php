<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, X-File-Type, X-File-Name, X-File-Size, Content-Type, Accept");
header("Access-Control-Allow-Credentials:false");

// Composer Autoloader
require_once(__DIR__ . '/vendor/autoload.php');

define('APP_ROOT_DIR',__DIR__);
define('MODULES_DIR',APP_ROOT_DIR . '/Modules');
define('CORE_TEST_DIR', APP_ROOT_DIR . '/Core/Tests' );

// Init DIC and App Wide Services
$di = new Phalcon\DI\FactoryDefault();

$di->set('config', function() {
	return include('Config/Config.php');
});
$config = $di->get('config');


//specify elastic search handler
$di->set('es', function() use ($config) {
	return  new \Elastica\Client( $config['esconfig'] );
});

//event manager
$di->setShared('eventsManager', function(){
	$evManager = new Phalcon\Events\Manager();
	$evManager->collectResponses(true);
	return  $evManager;
});

//profiler
$di->set('profiler', function(){
    return new \Phalcon\Db\Profiler();
}, true);

//database up
$di->set('db', function() use ($config,$di) {

    $connection = new Phalcon\Db\Adapter\Pdo\Mysql($config['db']);	

	//set sql logging
	if( isset( $config['logging']['enabled'] ) && true === $config['logging']['enabled'] 
	&& isset( $config['db']['logging']['file'] ) && !empty( $config['db']['logging']['file'] ) ){
	    $eventsManager = $di->get('eventsManager');

	    //Get a shared instance of the DbProfiler
	    $profiler = $di->getProfiler();
	    	
	    $logger = new Phalcon\Logger\Adapter\File( $config['db']['logging']['file'] );
	
	    //Listen all the database events
	    $eventsManager->attach('db', function($event, $connection) use ($profiler,$logger) {
	        if ($event->getType() == 'beforeQuery') {
	            $profiler->startProfile($connection->getSQLStatement());
	        }
	        if ($event->getType() == 'afterQuery') {
	            $profiler->stopProfile();
	            
	            $logger->log($connection->getSQLStatement() . ' | Query Time: '. $profiler->getTotalElapsedSeconds() , Phalcon\Logger::INFO);
	        }
	    });
	
	    //Assign the eventsManager to the db adapter instance
	    $connection->setEventsManager($eventsManager);
	}
    return $connection;
});

$di->set('log', function() use ($config) {
	$logger =  new Phalcon\Logger\Adapter\File( $config['logging']['file'] );
	return $logger;
});

$di->set('assets',function() use ($config){
	return new Phalcon\Assets\Manager();
},true);

$di->set('view',function(){
	return new Phalcon\Mvc\View\Simple();
});
/*
$di['modelsMetadata'] = function() {

    // Create a meta-data manager with APC
    $metaData = new \Phalcon\Mvc\Model\MetaData\Apc(array(
        "lifetime" => 86400,
        "prefix"   => "media"
    ));

    return $metaData;
};
*/
// Start Phalcon
$app = new Phalcon\Mvc\Micro($di);


$debug = new \Phalcon\Debug();
$debug->listen();

//Load All Module Routes
foreach( scandir( MODULES_DIR ) as $m) {

	//If you need to define some di or routes, do it with
	if( '.' == $m || '..' == $m ) continue;
	$_register = MODULES_DIR . '/' . $m . '/Config/Register.php';
	if( is_readable($_register) ){
		include_once( $_register );
	}
}

// App Wide Routes
$app->notFound(function () use ($app) {
	$app->response->setStatusCode(404, "Not Found")->sendHeaders();
	echo '<h2>This is crazy, but this page was not found!</h2>';
});

//update config di
$di->set('config',function() use ($config){ 
	return $config;
});

$app->after(function() use ($app) {
    //This is executed after the route was executed
    if($app->getReturnedValue()){
    	if($app->request->hasPost('callback')){
	    	echo $app->request->getPost('callback').'('.json_encode($app->getReturnedValue()).');';
    	}elseif($app->request->hasQuery('callback')){
	    	echo $app->request->getQuery('callback').'('.json_encode($app->getReturnedValue()).');';
    	}else{
			echo json_encode($app->getReturnedValue());	
    	}
    	
    }
});

//Auto Loader
$loader = new \Phalcon\Loader();
$loader->registerNamespaces($config['namespaces']);
// register autoloader
$loader->register();


Phalcon\DI::setDefault($di);