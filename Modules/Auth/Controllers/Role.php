<?php
namespace Auth\Controllers;
/*
 * The Role Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class Role extends BaseController{


	public function newRole( $name, $group_id ){
		
		$exists = \Auth\Models\Role::findFirst(array(
			"name = :name:".
			"bind"=>array(
				"name"=>$name
			)
		));
		
		if( $exists ){
			$newRole = new \Auth\Models\Group();
			$newRole->name = $name;
			$newRole->group_id = $name;
			$newRole->save();
			return $newRole;
		}else{
			return array('status'=>'error','message'=>"Role Exists");
		}
	}
	
	public function delete( $role_id ){
		$role = \Auth\Models\Role::findFirst($role_id);
		
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
		$currentCapabilities = \Auth\Models\RoleCapabilites::find(array(
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
			$nrcap = new \Auth\Models\RoleCapabilites();
			$nrcap->role_id = $role_id;
			$nrcap->capability_id = $c;
			$nrcap->save();
		}
		$role = \Auth\Models\Role::findFirst($role_id);
		$role->capabilities = $role->Capabilites();
		return $role;
	}

}