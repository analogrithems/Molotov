<?php
namespace Auth\Controllers;
/*
 * The Group Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class Group extends BaseController{

	public function newGroup( $name, $firstUser ){
		
		$exists = \Auth\Models\Group::findFirst(array(
			"name = :name:".
			"bind"=>array(
				"name"=>$name
			)
		));
		
		if( $exists ){
			$newGroup = new \Auth\Models\Group();
			$newGroup->name = $name;
			$newGroup->save();
			
			if($newGroup->id){
				$userGroup = new \Auth\Models\UserGroups();
				$userGroup->user_id = $firstUser;
				$userGroup->group_id = $newGroup->id;
				$userGroup->role_id = 1;//groupAdmin
				$userGroup->save();
				die(print_r($userGroup->serialize(),1));
			}
			
		}else{
			return array('status'=>'error','message'=>"Group Exists");
		}
	}
	
	public function userList( $group_id ){
		$users = \Auth\Models\UserGroups::find(array(
			"group_id = :group_id:",
			"bind"=>array(
				"group_id"=>$group_id
			)
		));
		$members = array();
		foreach( $users as $user ){
			$u = $user->getUser()->serialize();
			$r = $user->getRole()->serialize();
			$members[] = array( 
				'user'=>$u,
				'role'=>$r
			);
		}
		return $members;
	}
}