<?php

namespace Molotov\Core\Lib;

class PubSub 
{
	protected static $subscriptions;

	public function __construct() {}
	
	public static function subscribe($name, $callback){	
		static::$subscriptions[$name][] = $callback;
	}

	public static function publish($name, $params = array()){
		if(empty(static::$subscriptions[$name])) {
			return false;
		}
		foreach(static::$subscriptions[$name] as $event) {
			call_user_func_array($event, $params);
		}

	}

	public static function unsubscribe($name){
		if(!empty($this->subscriptions[$name])) {
			unset($this->subscriptions[$name]);
		}
	}

	public static function getSubscriptions() {
		return static::$subscriptions;
	}
}