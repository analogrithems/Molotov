<?php

namespace Molotov\Core\Controllers;

class BaseController extends \Phalcon\Mvc\Controller{

	public function debug( $msg ){
		$logger = $this->di->get('log');
		$logger->log( $msg, \Phalcon\Logger::INFO );
	}

	public function error( $msg ){
		$logger = $this->di->get('log');
		$logger->log( $msg, \Phalcon\Logger::ERROR );
	}	

}