<?php
namespace Molotov\Modules\Auth\Controllers;
/*
 * The Auth Controller
 */
use Molotov\Modules\Auth\Controllers\CapabilityController;
use Molotov\Modules\Auth\Controllers\GroupController;
use Molotov\Modules\Auth\Controllers\RoleController; 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Capability;

class AuthController extends BaseController{
	
	
	public function getCapabilities(){
		$capabilityController = new CapabilityController();
		$caps = $capabilityController->getCapabilities();
		$result = array();
		foreach($caps as $cap){
			$result[] = array( 'id'=>$cap->id, 'name'=>$cap->capability );
		}
		return $result;
	}
	
	public function login(){	
		$email 		= strtolower($this->di->get('request')->get('email'));
		$password 	= $this->di->get('request')->get('password');
	
		if( $this->di->get('session')->login($email,$password) ){
			$user = $this->di->get('session')->user;
			$user->groups = $this->di->get('session')->getGroupRoles();
			return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
		}else{
			return array('status'=>'error', 'msg'=>"Username or password incorrect");
		}
	}
	
	public function SessionCheck(){
		if( $this->di->get('session')->sessionCheck() ){
			$user = $this->di->get('session')->user;
			$user->groups = $this->di->get('session')->getGroupRoles();
			return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
		}else{
			return array('status'=>'error', 'msg'=>"Not Logged In");
		}
	}

	public function logout(){
		if( $this->di->get('session')->logout() ){
			return array('status'=>'ok');
		}else{
			return array('status'=>'error', 'msg'=>"Logout Failed");
		}
	}

	public function addUser(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation->setFilters('password', 'trim');
		$validation->setFilters('email', 'trim');
		$validation->setFilters('display_name', 'trim');
		
		$args['email'] = $this->di->get('request')->get('email');
		$args['password']= $this->di->get('request')->get('password');
		$args['display_name']= $this->di->get('request')->get('display_name');
		$_user = new Molotov\Modules\Auth\Controllers\UserController();
		$result = $_user->action_addUser($args);
		return $result;
		
	}
	
	public function passwordResetRequest(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation->setFilters('email', 'trim');
		
		$args['email'] = $this->di->get('request')->get('email');
		
		$_user = new Molotov\Modules\Auth\Controllers\UserController();
		$result = $_user->action_passwordResetRequest($args);
		return $result;
		
	}
	
	public function PasswordReset(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation->setFilters('email', 'trim');
		$validation->setFilters('password', 'trim');
		$validation->setFilters('password_confirm', 'trim');
		$validation->setFilters('activation_key', 'trim');
		
		if( $this->di->get('request')->get('password') == $this->di->get('request')->get('password_confirm')){
			$args['email'] = $this->di->get('request')->get('email');
			$args['password']= $this->di->get('request')->get('password');
			$args['activation_key']= $this->di->get('request')->get('activation_key');
			$_user = new Molotov\Modules\Auth\Controllers\UserController();
			$result = $_user->action_passwordReset($args);
			return $result;
		}else{
			return array('status'=>'error','msg'=>"Passwords do not match");
		}
		
	}


	public function ActivateUser(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation
		    ->add('activation_key', new \Phalcon\Validation\Validator\PresenceOf(array(
		        'message' => 'The activation key is required'
		    )))
		    ->add('email', new \Phalcon\Validation\Validator\Email(array(
		        'message' => 'The email is invalid'
		    )))
		    ->add('email', new \Phalcon\Validation\Validator\PresenceOf(array(
		        'message' => 'The email is required'
		    )));
		$validation->setFilters('activation_key', 'trim');
		$validation->setFilters('email', 'trim');
		
		$args['email'] = $this->di->get('request')->get('email');
		$args['activation_key']= $this->di->get('request')->get('activation_key');
		$user = new Molotov\Modules\Auth\Controllers\UserController();
		$result = $user->action_activateUser($args);
		return $result;
	}
	

	public function getGroup(){
		$name = $this->di->get('request')->get('name');
		$_user = $this->di->get('session')->user;
		
		$group = new \Auth\Controllers\GroupController();
		$result = $group->newGroup($name, $_user->id);
		return $result;
	}


	public function myGroups(){
		$myGroups = array();
		$_groupRoles = $this->di->get('session')->getGroupRoles();
		foreach( $_groupRoles as $gr ){
			$myGroups[] = array( 'group'=>$gr->getGroup(), 'role'=> $gr->getRole() );
		}
		
		return $myGroups;
	}
	

	public function getMembers( $id ){
		$members = array();
		$groupController = new GroupController();
		foreach( $groupController->userList($id) as $mem ){
			$members[] = array(
				'id'=>$mem['user']['id'],
				'display_name'=>$mem['user']['display_name'],
				'email'=>$mem['user']['email'],
				'created'=>$mem['user']['created'],
				'role'=>$mem['role']
			);
		}
		
		return array('status'=>'ok','members'=>$members);
	}

	public function getRole(){
		$name = $this->di->get('request')->get('name');
		$group_id = $this->di->get('request')->get('group_id');
		if( $this->di->get('session')->can('manage_group',$group_id) ){
			$roleContoller = new RoleController();
			$result - $roleContoller->addRole($name,$group_id);
		}else{
			return array('status'=>'error','message'=>'Permission Denied');
		}
		
		$group = new GroupController();
		$result = $group->newGroup($name, $_user->id);
		return $result;
	}
	
}