<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;
use Molotov\Modules\Auth\Models\Role;
use Molotov\Modules\Auth\Models\Capability;
use Molotov\Modules\Auth\Models\RoleCapabilites;

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
	
	/**
	 * a handful of things need to be done when a group is created such as building out the default roles and capabilities
	 */
	public function afterCreate(){
		//create default roles for this new group
		include_once(AUTH_MODULE_DIR.'/data/default_roles.php');
		foreach($default_roles as $name=>$caps){
			$role = new Role();
			$role->group_id = $this->id;
			$role->name = $name;
			$role_caps = array();
			foreach($caps as $_cap){
				$cap = Capability::findFirst("capability = '{$_cap}'");
				$role_caps[] = $cap;
			}
			$role->capabilities = $role_caps;
			$role->save();
		}
	}
}