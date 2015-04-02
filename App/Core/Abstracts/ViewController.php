<?php
namespace Molotov\Core\Abstracts;

use Phalcon\Mvc\View\Simple as View;

abstract class ViewController extends View
{
	protected $module;

	public function __construct()
	{
		parent::__construct();
		$this->di = \Phalcon\DI::getDefault();
		$this->setDI($this->di);
		$this->registerEngines(array(
			'.view' => 'Phalcon\Mvc\View\Engine\Volt'
		));
	}

	public function render($path, $params = array())
	{
		$config = $this->di->get('config');
		
		//check for required config var or bomb and give error
		if(!property_exists($config,'compiled_views')){
			die("Your config is missing the compiled_views setting.  This is required for generating static views");
		}
		
		$exempt_cache_path = array('invalid_key','activation_welcome','reset_password','user_activation','been_activated');
		
		//insure static dir exists or write it or error
		if($this->di->get('router') && $this->di->get('router')->getMatchedRoute() && !in_array($path, $exempt_cache_path)){
			$compiledPattern = $this->di->get('router')->getMatchedRoute()->getCompiledPattern();			
		}else{
			$compiledPattern = get_class($this).'/'.$path;
		}

		if( $compiledPattern == '/' ){
			$compiledPattern = 'index';
		}
		$static_file = $config->compiled_views.'/'.$compiledPattern.'.html';
		if(!file_exists(dirname($static_file))){
			@mkdir(dirname($static_file),0755,true);
		}
		
		if( !$config->always_recompile && file_exists($static_file) && ('email_invite' != $path && 'email_uninvite' != $path ) ){
			return file_get_contents($static_file);
		}else{
			$source = parent::render($path, $params);
			$source = $this->compileScripts($source);
			$source = $this->compileAssets($source);
			$source = $this->compileStylesheets($source);
			//write static contents out to file path
			file_put_contents($static_file,$source);
		}

		return $source;
	}


	public function setModule($module)
	{
		$this->module = $module;
	}

	public function compileScripts($source)
	{
		$list = array();
		preg_match_all("/{JS+.*}/", $source, $matches);
		foreach($matches[0] as $match) {
			$source = str_replace($match, '', $source);
			$match = str_replace('{JS', '', $match);
			$match = str_replace('}', '', $match);
			$match = str_replace(' ', '', $match);
			$scripts = explode(',', $match);
			$list = array_merge($scripts, $list);
		}
		$config = $this->di->get('config');
		$jsfile = $this->di->get('js')->serve( AR_ROOT .'/'. $config->compiled_views . '/js/'.$this->module .'/', $list, !$config->always_recompile);
		$asset = $config->url . $config->compiled_views .'/js/'.$this->module .'/'.$jsfile;
		$source = str_replace('{SCRIPTS}', $asset, $source);
		return $source;
	}

	public function compileAssets($source)
	{
		preg_match_all("/{ASSET+.*}/", $source, $matches);
		foreach($matches[0] as $match) {
			$asset = str_replace('{ASSET', '', $match);
			$asset = str_replace('}', '', $asset);
			$asset = str_replace(' ', '', $asset);
			$parts = explode('/', $asset);
			$type = $parts[0];
			$asset = $parts[1];
			$config = $this->di->get('config');
			$asset = $config->url . 'asset/' . $this->module .'/'. $type .'/'.$asset;
			$source = str_replace($match, $asset, $source);
		}
		return $source;
	}

	public function compileStylesheets($source)
	{
		$list = array();
		preg_match_all("/{CSS+.*}/", $source, $matches);
		foreach($matches[0] as $match) {
			$source = str_replace($match, '', $source);
			$match = str_replace('{CSS', '', $match);
			$match = str_replace('}', '', $match);
			$match = str_replace(' ', '', $match);
			$scripts = explode(',', $match);
			$list = array_merge($scripts, $list);
		}
		$list = array_reverse($list);
		$config = $this->di->get('config');
		$cssfile = $this->di->get('style')->serve(AR_ROOT .'/'. $config->compiled_views . '/sass/'.$this->module .'/', $this->getViewsDir() . '../sass/', $list, !$config->always_recompile);
		$asset = $config->url . $config->compiled_views .'/sass/'.$this->module .'/'.$cssfile;
		$source = str_replace('{STYLES}', $asset, $source);
		return $source;
	}
	public function jsonResponse($content)
	{
		$request = $this->di->get('request');
		$callback = $request->get('callback');

		$response = $this->di->get('response');
		
		$response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
		$expireDate = new \DateTime();
		$expireDate->modify('-10 minutes');
		
		$response->setExpires($expireDate);
		
		if($callback) {
			$response->setContentType('application/javascript', 'UTF-8');
			$response->setContent($callback . "('" . json_encode($content,JSON_UNESCAPED_UNICODE) . "')");
		} else {
			$response->setContentType('application/json', 'UTF-8');
			$response->setContent(json_encode($content,JSON_UNESCAPED_UNICODE));
		}
		return $response;
	}
	public function htmlResponse($content){
		$response = $this->di->get('response');
		$response->setContentType('text/html', 'UTF-8');
		$response->setContent($content);

		return $response;
	}
}
