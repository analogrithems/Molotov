<?php
namespace Molotov\Core\Abstracts;

abstract class Model
{
	protected $di;
	protected $def = array();
	private $fields = array();

	public function __construct($data = null)
	{
		if(is_object($data) && method_exists($data,'out')){
			$data = $data->out();
		}
		$this->di = \Phalcon\DI::getDefault();
		$this->def = static::buildDefinition();
		//preload defaults
		$this->serialize(null);
		//overwrite the defaults with data
		$this->serialize($data);
	}

	public function serialize($data)
	{
		$def = static::buildDefinition();
		if($data == null) {
			foreach($def as $k => $v) {
				$this->{$k} = $v;
			}
			return;
		}
		if(is_array($data)) {
			foreach($def as $k => $v) {
				if(array_key_exists($k, $data)) {
					$this->{$k} = $data[$k];
				}
			}
		}
	}

	public function unserialize($fields = null) 
	{
		if(!$fields) {
			$fields = array();
			foreach($this->def as $k => $v) {
				$fields[] = $k;
			}
		}
		if( !is_array($fields) ) {
			$a = array();
			$a[] = $fields;
			$fields = $a;
		}

		$out = array();
		foreach($fields as $field) {
			if(array_key_exists($field, $this->def)) {
				$out[$field] = $this->{$field};
			}
		}
		return $out;
	}

	protected static function buildDefinition()
	{
		$fields = array();
		$class = get_called_class();
		$parent = new \ReflectionClass($class);
		do {
			$properties = $parent->getDefaultProperties();
			foreach($properties['fields'] as $k => $v) {
				if(array_key_exists($k, $fields)) {
					unset($properties['fields'][$k]);
				}
			}
			$fields = array_merge($fields, $properties['fields']);
		} while($parent = $parent->getParentClass());
		return $fields;
	}
	
	public static function search( $request ){
		return false;
	}
	
}