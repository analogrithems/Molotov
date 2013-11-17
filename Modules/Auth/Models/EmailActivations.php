<?php
namespace Auth\Models;
/*
 * The Media used by our system
 */
 
use \Molotov\Core\Models\BaseModel;

class EmailActivations extends BaseModel{

	public $fields = array(
		'id',
		'user_id',
		'activation_key',
		'type',
		'created',
		'used'
	);
	
	public function getSource()
	{
		$config   = 	$this->_dependencyInjector->get('config');
		return $config['db']['table_prefix'] . 'emailactivations';
	}
	
	public function initialize()
	{
	        $this->belongsTo("user_id", "\Auth\Models\User", "id");
	}

	public function beforeValidationOnCreate()
	{
		$this->activation_key = uniqid(sha1(rand()),true);
		$this->created = date('Y-m-d H:i:s');
		$this->used = 0;
	}
	
	public function validation()
	{		
		$this->validate(new \Phalcon\Mvc\Model\Validator\Numericality(array(
			'field' => 'user_id'
		)));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\PresenceOf(array(
			'field' => 'activation_key'
		)));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\InclusionIn(array(
			'field' => 'type',
			'domain' => array('verify', 'passwordreset', 'signup')
		)));
		
		$this->validate(
			new \Phalcon\Mvc\Model\Validator\Uniqueness(
				array(
					"field"   => "activation_key",
					"message" => "This activation_key is already in use, try again"
				)
			)
		);
		
		if ($this->validationHasFailed() == true) {
			return false;
		}
	}
}