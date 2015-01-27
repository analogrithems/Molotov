<?php
namespace Molotov\Modules\Auth\Controllers;
/*
 * The Role Controller
 */
 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Auth\Models\Group;
use Molotov\Modules\Auth\Models\Role;
use Molotov\Modules\Auth\Models\RoleCapabilites;

 
class RoleController extends BaseController{


	public function newRole( $name, $group_id ){
		
		$exists = Role::findFirst(array(
			"name = :name:".
			"bind"=>array(
				"name"=>$name
			)
		));
		
		if( $exists ){
			$newRole = new Group();
			$newRole->name = $name;
			$newRole->group_id = $name;
			$newRole->save();
			return $newRole;
		}else{
			return array('status'=>'error','message'=>"Role Exists");
		}
	}
	
	public function delete( $role_id ){
		$role = Role::findFirst($role_id);
		
		return $role->delete();
	}
	
	/*
	* setCapabilities defines all the capabilities of a given role
	*
	* @param int $role_id the role_id 
	* @param array $capabilities list of the capabilities
	* @return Role
	*/
	public function setCapabilities( $role_id, $capabilities ){
	
		if( !is_array($capabilities) ) return false;
		//first remove curent capabilities
		$currentCapabilities = RoleCapabilites::find(array(
			"role_id = :role_id:",
			"bind"=>array(
				"role_id"=>$role_id
			)
		));
		
		foreach( $currentCapabilities as $cc ){
			$cc->delete();
		}
		
		
		//now set them to whatever is being set
		foreach( $capabilities as $c ){
			$nrcap = new RoleCapabilites();
			$nrcap->role_id = $role_id;
			$nrcap->capability_id = $c;
			$nrcap->save();
		}
		$role = Role::findFirst($role_id);
		$role->capabilities = $role->Capabilites();
		return $role;
	}

}