<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
 
use Molotov\Core\Models\BaseModel;

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
			"Molotov\Modules\Auth\Models\UserGroups",
			"group_id", "user_id",
			"Molotov\Modules\Auth\Models\User",
			"id",
			array('alias'=>'Users')
		);
	}
}