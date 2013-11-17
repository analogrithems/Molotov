<?php

namespace Molotov\Core\Models;

class BaseModel extends \Phalcon\Mvc\Model{
	protected $fields;

	public function getSource()
	{
		$config   = 	$this->_dependencyInjector->get('config');
		return $config['db']['table_prefix'] . parent::getSource();
	}
	
	/*
	* Magic functions to set the model properties based of an acl style
	* fields array above
	*/
	public function __set($field, $value){
		if(	in_array( $field, $this->fields ) ){
			$this->{$field} = $value;
		}
	}

	/*
	* Magic functions to set the model properties based of an acl style
	* fields array above
	*/
	public function __get($field){
		if(	in_array( $field, $this->fields ) ) return $this->{$field};
	}
	
	/*
	 * egressEvent  - This event is an optional event that can/should be defined in each
	 * model that extends the base model. This feature is intended
	 * to allow for optional saftey checks on a request after the data is fetched.  Also useful for 
	 * calling events once a model has been written
	 */
	 protected function egressEvent(){
	 
	 }
	 
	/*
	 * ingressEvent  - This event is an optional event that can/should be defined in each
	 * model that extends the base model. This feature is intended
	 * to allow for optional saftey checks on a request before the data is fetched.  Also useful for 
	 * calling events before a model has been written.
	 */
	 protected function ingressEvent(){
	 
	 }	 	
	
 	/*
 	 * serialize - define how you serialize models.  You can pass it a 
 	 * hash reference array to define what the returned serialized model looks like
 	 */
 	public function serialize($map = null){
	 	$serialize = array();

	 	foreach($this->fields as $key){
	 		if( isset( $this->$key ) ){
		 		$value = $this->$key;
	 		}else{
		 		$value = null;
	 		}
	 		$subMap = null;
	 		
	 		if( !is_null( $map ) ){
		 		if( isset( $map[$key]) ){
			 		$key = $map[$key];
			 		$subMap = (is_array($key)) ? $key : null;
		 		}elseif( in_array($key, $map) ){
			 		$key = $key;
		 		}else{
			 		continue;
		 		}
	 		}

		 	if( is_array( $value ) ){
			 	foreach( $value as $_k=>$_v ){
				 	if( is_object( $_v ) ){
				 		if( method_exists( $_v, 'serialize' ) ){
							$_s = $_v->serialize( $subMap );
							$serialize[$key][] = $_s;
				 		}else{
				 			$serialize[$key][] =  json_encode( $_v );//how do we send sub map?
				 		}
				 	}else{
					 	array_push( $serialize[$key], $_v );
				 	}
			 	}
		 	}elseif( is_object( $value ) ){
			 	$cn = get_class($value);
			 	if(preg_match('/Capability/i',$cn) > 0){
				 	die(print_r($value));
			 	}
			 	if( is_subclass_of($value, 'Molotov\Core\Models\BaseModel')){
				 	$ss = $value->serialize($subMap);
			 		$serialize[$key] = $ss;
			 	}else{
			 		$serialize[$key] = json_encode($value);
			 	}
		 	}else{
			 	$serialize[$key] = $value;
		 	}
	 	}
	 	
	 	return $serialize;
 	}
	
	/**
	* unserialize is also thought of as hydrate.  Take some chunk of data and populate our models with the data
	*
	*/
	public function unserialize( $dataIn = array() ){
		foreach($dataIn as $key => $value ){
			if( in_array($key, $this->fields ) ) $this->{$key} = $value;
		}
		
	}
	
	public function debug( $msg ){
		$logger = $this->di->get('log');
		$logger->log( $msg, \Phalcon\Logger::INFO );
	}

	public function error( $msg ){
		$logger = $this->di->get('log');
		$logger->log( $msg, \Phalcon\Logger::ERROR );
	}		

}