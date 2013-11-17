<?php
namespace Auth\Models;
/*
 * The Media used by our system
 */
 
use \Molotov\Core\Models\BaseModel;

class Group extends BaseModel{

	protected $fields = array(
		'id',
		'name',
		'role'
	);
	
	public function initialize()
	{
		$this->hasManyToMany(
			"id",
			"\Auth\Models\UserGroups",
			"group_id", "user_id",
			"\Auth\Models\User",
			"id",
			array('alias'=>'Users')
		);
	}
}