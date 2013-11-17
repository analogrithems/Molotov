<?php
namespace Auth\Models;
/*
 * Standard user object
 */
use \Molotov\Core\Models\BaseModel;

class User extends BaseModel{
	public $id;

	protected $fields = array(
		'id',
		'display_name',
		'email',
		'password',
		'enabled',
		'created',
		'groups'
	);
	
	public function validation()
	{		
		$this->validate(new \Phalcon\Mvc\Model\Validator\PresenceOf(
			array(
				'field' => 'password',
				'message'=>"password can not be empty"
			)
		));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\PresenceOf(array(
			'field' => 'display_name'
		)));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\Email(array(
			'field' => 'email'
		)));
		
		$this->validate(
			new \Phalcon\Mvc\Model\Validator\Uniqueness(
				array(
					"field"   => "email",
					"message" => "This email is already in use"
				)
			)
		);

		if ($this->validationHasFailed() == true) {
			return false;
		}
	}
	
	public function initialize()
	{
		$this->hasManyToMany(
			"id",
			"\Auth\Models\UserGroups",
			"user_id", "group_id",
			"\Auth\Models\Group",
			"id",
			array('alias'=>'groups')
		);
		
		$this->hasManyToMany(
			"id",
			"\Auth\Models\UserGroups",
			"user_id", "role_id",
			"\Auth\Models\Role",
			"id",
			array('alias'=>'roles')
		);
	}
}