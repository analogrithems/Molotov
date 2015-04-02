<?php
use Swagger\Annotations as SWG;
/**
 * @SWG\Info(
 *   title="Molotov Framework",
 *   description="This is the Molotv framework used to provide to provide high performance enterprise services",
 *   contact="analogrithems@gmail.com",
 *   license="Apache 2.0",
 *   licenseUrl="http://www.apache.org/licenses/LICENSE-2.0.html"
 * )
 *
 * @SWG\Authorization(
 *   type="apiKey",
 *	 passAs="query",
 * 	 keyname="MOLOTOV"
 * )
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, X-File-Type, X-File-Name, X-File-Size, Content-Type, Accept");
header("Access-Control-Allow-Credentials:false");

// Composer Autoloader
require_once(__DIR__ . '/vendor/autoload.php');

define('APP_ROOT_DIR',__DIR__.'/');
define('MODULES_DIR',APP_ROOT_DIR . '/App/Modules');
define('CORE_TEST_DIR', APP_ROOT_DIR . '/App/Core/Tests' );

// Init DIC and App Wide Services
$di = new Phalcon\DI\FactoryDefault();

$di->setShared('config', function() {
	return include('Config/Config.php');
});
$config = $di->get('config');


//specify elastic search handler
/*
$di->set('es', function() use ($config) {
	return  new \Elastica\Client( $config['esconfig'] );
});
*/

$di->set('security', function(){

    $security = new Phalcon\Security();

    //Set the password hashing factor to 12 rounds
    $security->setWorkFactor(12);

    return $security;
}, true);

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
$di->setShared('db', function() use ($config,$di) {

	switch($config['db']['driver']){
		case 'Mysql':
		    $connection = new Phalcon\Db\Adapter\Pdo\Mysql($config['db']['creds']);
		    break;
		case 'Postgresql':
			$connection = new \Phalcon\Db\Adapter\Pdo\Postgresql($this->db_args);
			break;
		default:
			if( isset($config['db']['creds']['dbname']) &&  !file_exists($config['db']['creds']['dbname']) ){
				if( !file_exists(dirname($config['db']['creds']['dbname']))){
					mkdir(dirname($config['db']['creds']['dbname']),0750,true);
				}
			}
			$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($this->db_args);
			break;
	}

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

$di->setShared('timezone', function(){
	return function($val){
		$dateTime = new DateTime();
		if( !$val )
			$r = '';
		elseif( $val == 'undefined' )
			$r = 'undefined';
		else {
			$dateTime->setTimeZone( new DateTimeZone($val));
			$r = $dateTime->format('T');
		}
		return $r;
	};
});
$di->setShared('log', function() use ($config) {

	switch($config['logging']['log_driver']){
		case 'Firephp':
			return new \Phalcon\Logger\Adapter\Firephp("");
		case 'File':
			return new Phalcon\Logger\Adapter\File( $config['logging']['file'] );
		default:
			return new Phalcon\Logger\Adapter\Syslog('Molotov');
	}
});

$di->setShared('utils',function(){
	return new Molotov\Core\Lib\Utils();
});

$di->setShared('html_sanitize',function(){
	return new Molotov\Core\Lib\HTMLSanitize();
});

$di->set('assets',function() use ($config){
	return new Phalcon\Assets\Manager();
});

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

/*
set_exception_handler(function($e)
{
    $p = new \Phalcon\Utils\PrettyExceptions();
    return $p->handle($e);
});

set_error_handler(function($errorCode, $errorMessage, $errorFile, $errorLine)
{
    $p = new \Phalcon\Utils\PrettyExceptions();
    return $p->handleError($errorCode, $errorMessage, $errorFile, $errorLine);
});
*/

//We now have a message queue
$di->setShared('queue', function() use ($config,$di){

	$_url = parse_url($config['site_url']);
	$prefix = strstr($_url['host'],'.',1).'_';

	return new Phalcon\Queue\Beanstalk\Extended(array(
		'host'=>$config['queue_host'],
		'prefix'=>$prefix,
		'logger'=>$di->get('log')
	));
});

//Create Email Queue, usually you'd add a queue in a module

$di->get('queue')->addWorker('email','Molotov\Core\Lib\Email::sendEmail');

$di->setShared('pubsub', function() {
	return new Molotov\Core\Lib\PubSub();
});

$di->setShared('pages', function() use ($app){
	return new Molotov\Core\Lib\Pages($app);
});

$di->set('request', 'Phalcon\Http\Request', true);
 
//Load All Module Routes
foreach( scandir( MODULES_DIR ) as $m) {

	//If you need to define some di or routes, do it with
	if( '.' == $m || '..' == $m ) continue;
	
	
	$_register = MODULES_DIR . '/' . $m . '/Config/Register.php';
	if( is_readable($_register) ){
		include_once( $_register );
	}
	
	$module_file = MODULES_DIR . '/' . $m . "/{$m}Module.php";
	if(is_readable($module_file)){
		$class = 'Molotov\Modules\\' . $m . '\\' . $m .'Module';
		$module = new $class($m);
		$app->mount($module);
	}else{
		//\Phalcon\DI::getDefault()->get('log')->warning("No Module file for {$module_file}");
	}
}

// App Wide Routes
$app->notFound(function () use ($app) {
	$app->response->setStatusCode(404, "Not Found")->sendHeaders();
	\Phalcon\DI::getDefault()->get('log')->warning("No route found");
	echo '<h2>I know I just met you, and this is crazy, but this page was not found! Sorry baby...</h2>';
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


