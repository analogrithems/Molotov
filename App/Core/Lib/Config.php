<?php
namespace Arez\Core\Lib;

class Config extends \Phalcon\Config
{
	public function __construct() 
	{
		$this->setEnvironment();
		$config_json = $this->app_path . '/../../config.json';
		$config_php = $this->app_path . '/../../config.php';
		if(file_exists($config_json)) {
			$json_settings = json_decode(file_get_contents($config_json), 1);
			if(!empty($json_settings)) $this->loadSettings($json_settings);
		}else if(file_exists($config_php)) {
			include($config_php);
			if(!empty($settings)){
				$this->loadSettings($settings);
			}
		}
		$this->loadModules($this->modules);
	}
	
	protected function setEnvironment()
	{
		$this->app_path = __DIR__ . '/../../';
		$this->public_path = $this->app_path . '../../public/';
	}
	
	public function loadModules($modules)
	{
		foreach($modules as $module) {
			$config_json = $this->app_path . '/Modules/' . $module . '/config.json';
			
			if(file_exists($config_json)) {
				$settings = json_decode(file_get_contents($config_json), 1);
				$this->loadSettings($settings);
			}
			
			$config_php = $this->app_path . '/Modules/' . $module . '/config.php';
			if(file_exists($config_php)) {
				$settings = include_once($file);
				$this->loadSettings($settings);
			}
		}	
	}
	
	protected function loadSettings($settings)
	{
		foreach($settings as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function __get($value)
	{
		return $this->$value;
	}

	public function __set($key, $value)
	{
		$this->$key = $value;
	}
}