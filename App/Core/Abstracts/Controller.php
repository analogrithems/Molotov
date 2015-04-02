<?php

namespace Molotov\Core\Abstracts;

abstract class Controller 
{
	protected $di;
	protected $request;

	public function __construct()
	{
		$this->di = \Phalcon\DI::getDefault();
	}

	public function setModule($module)
	{
		$this->module = $module;
	}
	
	public function jsonResponse($content){
		$request = $this->di->get('request');
		$callback = $request->get('callback');

		$response = $this->di->get('response');
		
		$response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
		$expireDate = new \DateTime();
		$expireDate->modify('-10 minutes');
		
		$response->setExpires($expireDate);

		if($callback) {
			$content = $callback . "('" . json_encode($content,JSON_UNESCAPED_UNICODE) . "')";
			$response->setHeader('Content-length',strlen($content));
			$response->setContentType('application/javascript', 'UTF-8');
			$response->setContent($content);
		} else {
			$content = json_encode($content,JSON_UNESCAPED_UNICODE);
			$response->setHeader('Content-length',strlen($content));
			$response->setContentType('application/json', 'UTF-8');
			$response->setContent($content);
		}
		return $response;
	}

}