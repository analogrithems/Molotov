<?php
namespace Molotov\Modules\Auth\Models;
/*
 * User Role
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;
use Molotov\Modules\Auth\Models\Role;


/**
 * @SWG\Model(id="UserGroups")
 */
class UserGroups extends BaseModel{
	
	protected $fields = array(
		'id',
		'user_id',
		'group_id',
		'role_id'
	);
	
	/**
	 * finds a role in a specific group
	 *
	 * @param string $title
	 * @return Molotov\Modules\Auth\Models\Role
	 */
	public function findRoleByName( $title = null ){
		$role = Role::findFirst(array(
			'conditions'=>"group_id=:group_id: and name=:name:",
			'bind'=>array(
				'group_id'=>$this->group_id,
				'name'=>$title
			)
		));
		if($role){
			return $role;
		}else{
			return false;
		}
	}
	
	
	/**
	 * find a specific role and set it to the current record
	 *
	 * @param string $title
	 * @return bool
	 */
	public function setRole( $title ){
		$role = $this->findRoleByName($title);
		if($role){
			$this->role_id = $role->id;
		}else{
			return false;
		}
	}
	
	public function getSource()
	{
		return 'user_groups';
	}

	
	public function initialize()
	{
		$this->belongsTo("user_id", "Molotov\Modules\Auth\Models\User", "id",array('alias'=>'User'));
		$this->belongsTo("group_id", "Molotov\Modules\Auth\Models\Group", "id",array('alias'=>'Group'));
		$this->belongsTo("role_id", "Molotov\Modules\Auth\Models\Role", "id",array('alias'=>'Role'));
	}
}