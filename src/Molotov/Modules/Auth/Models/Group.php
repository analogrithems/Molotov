<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;

/**
 * @SWG\Model(id="Group")
 */
class Group extends BaseModel{

	/**
	 * @SWG\Property(name="id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="name",type="string")
	 */
	 
	/**
	 * @SWG\Property(name="role",type="Role")
	 */
	 
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