<?php
namespace Molotov\Modules\Auth\Models;
/*
 * Standard user object
 */
use Molotov\Core\Models\BaseModel;
use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Email;
use Phalcon\Mvc\Model\Validator\Uniqueness;

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
		$this->validate(new PresenceOf(
			array(
				'field' => 'password',
				'message'=>"password can not be empty"
			)
		));
		
		$this->validate(new PresenceOf(array(
			'field' => 'display_name'
		)));
		
		$this->validate(new Email(array(
			'field' => 'email'
		)));
		
		$this->validate(
			new Uniqueness(
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
			"Molotov\Modules\Auth\Models\UserGroups",
			"user_id", "group_id",
			"Molotov\Modules\Auth\Models\Group",
			"id",
			array('alias'=>'groups')
		);
		
		$this->hasManyToMany(
			"id",
			"Molotov\Modules\Auth\Models\UserGroups",
			"user_id", "role_id",
			"Molotov\Modules\Auth\Models\Role",
			"id",
			array('alias'=>'roles')
		);
	}
}