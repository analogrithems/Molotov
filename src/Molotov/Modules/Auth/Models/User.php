<?php
namespace Molotov\Modules\Auth\Models;
/*
 * Standard user object
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;
use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Email;
use Phalcon\Mvc\Model\Validator\Uniqueness;

/**
 * @SWG\Model(id="User")
 */
class User extends BaseModel{
	public $id;
	
	/**
	 * @SWG\Property(name="id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="display_name",type="string")
	 */
	 
	/**
	 * @SWG\Property(name="email",type="string")
	 */
	 
	/**
	 * @SWG\Property(name="password",type="string")
	 */
	 
	/**
	 * @SWG\Property(
	 *	name="group_id",
	 *	type="integer",
	 *	format="int64",
	 *	description="The users current group_id, in Molotov a user can be a member of multiple groups")
	 */
	 
    /**
     * @SWG\Property(
     *   name="enabled", type="integer", format="int32",
     *   description="User Status",
     *   enum="{'0':'disabled','1':'enabled'}"
     * )
     */
	 
	/**
	 * @SWG\Property(name="created",type="string",description="standard SQL timestamp in YYYY-MM-DD HH:MM:SS format")
	 */
	 
	/**
	 * @SWG\Property(name="groups",type="array",@SWG\Items("Group"))
	 */

	protected $fields = array(
		'id',
		'display_name',
		'email',
		'password',
		'group_id',
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