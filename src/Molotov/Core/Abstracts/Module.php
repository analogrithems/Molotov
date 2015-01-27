<?php
namespace Molotov\Core\Abstracts;

use Phalcon\Mvc\Micro\Collection;

abstract class Module extends Collection
{
	protected $di;
	protected $controller;
	protected $prefix = '/api';
	protected $routes = array();
	protected $services = array();
	protected $subscriptions = array();
	protected $workers = array();
	
	public function __construct($name=null){
		$this->di = \Phalcon\DI::getDefault();
		$controller = $this->controller;
		$this->setSubscriptions();
		$this->setServices();
		$this->setWorkers();
		if($controller) {
			$controller = new $controller();
			$controller->setModule($name);
			$this->setHandler($controller);
			$this->setRoutes();
		}
		$this->onConstruct();
	}

	public function setRoutes(){
		if(!$this->routes || !is_array($this->routes))
			return;
		foreach($this->routes as $route => $function) {
			$route = $this->prefix . $route;
			while(strstr($route, '../')) {
				$route = preg_replace('/\w+\/\.\.\//', '', $route);
			}
			$this->get($route, $function);
			$this->post($route, $function);
		}
	}

	public function setServices(){
		if(!$this->services || !is_array($this->services))
			return;
		foreach($this->services as $k=>$init) {
			if(is_array($init)) { 
				$class = $init[0];
				$function = $init[1];
				$init = $class::$function();
			}
			$this->di->setShared($k,$init);
		}
	}

	public function setSubscriptions(){
		if(!$this->subscriptions || !is_array($this->subscriptions))
			return;
		$pubsub = $this->di->get('pubsub');
		foreach($this->subscriptions as $array) {
			$pubsub::subscribe($array['event'], $array['callback']);
		}
	}
	
	public function setWorkers(){
		if(!$this->workers || !is_array($this->workers) || !$this->di->has('queue')){
			return;
		}

		foreach($this->workers as $k=>$init) {
			if(is_array($init)) { 
				$class = $init[0];
				$function = $init[1];
				$init = "{$class}::{$function}";
			}
			$this->di->get('queue')->addWorker($k, $init);
		}
	}
	
	public function onConstruct(){
		
	}
	
	
}