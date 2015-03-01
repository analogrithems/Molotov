<?php
namespace Molotov\Modules\Auth\Controllers;
/*
 * The Auth Controller
 */
use Swagger\Annotations as SWG;
use Molotov\Modules\Auth\Controllers\CapabilityController;
use Molotov\Modules\Auth\Controllers\GroupController;
use Molotov\Modules\Auth\Controllers\RoleController;
use Molotov\Modules\Auth\Controllers\UserController;
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Capability;

/**
 * @package Molotov
 * @subpackage AuthController
 *
 * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://development.asynonymous.net/Molotov/api",
 *   resourcePath="/Auth",
 *   description="Authentication actions"
 * )
 */

class AuthController extends BaseController{
	
    /**
     * @SWG\Api(
     *   path="/Auth/Capabilities",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Returns array of capabilities",
     *     notes="Get's a list of the users available capabilities.  It uses your role in your current group to decide this.",
     *     type="array"
     *   )
     * )
     */
	public function getCapabilities(){
		$capabilityController = new CapabilityController();
		$caps = $capabilityController->getCapabilities();
		$result = array();
		foreach($caps as $cap){
			$result[] = array( 'id'=>$cap->id, 'name'=>$cap->capability );
		}
		return $result;
	}
	
    /**
     * @SWG\Api(
     *   path="/Auth/Login",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Does a standard login with a given email & password",
     *     notes="Returns the authenticated user",
     *     type="User",
     *     authorizations={},
     *     @SWG\Parameter(
     *       name="email",
     *       description="email of the user requesting authentication",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="password",
     *       description="password for the user requesting authentication",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\ResponseMessage(code=403, message="Authentication Failed")
     *   )
     * )
     */
	public function login(){	
		$email 		= strtolower($this->di->get('request')->get('email'));
		$password 	= $this->di->get('request')->get('password');
	
		if( $this->di->get('session')->login($email,$password) ){
			$user = $this->di->get('session')->user;
			$user->groups = $this->di->get('session')->getGroupRoles();
			return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
		}else{
			//Getting a response instance
			$response = new \Phalcon\Http\Response();
			
			//Set status code
			$response->setStatusCode(403, "Authentication Failed");
			
			//Set the content of the response
			$response->setJsonContent(array('status'=>'error', 'msg'=>"Username or password incorrect"));
			
			//Send response to the client
			$response->send();
		}
	}
	
	
    /**
     * @SWG\Api(
     *   path="/Auth/SessionCheck",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Use this to see if you are already logged in and get user details",
     *     notes="Returns the authenticated user",
     *     type="User"
     *   )
     * )
     */
	public function SessionCheck(){
		if( $this->di->get('session')->sessionCheck() ){
			$user = $this->di->get('session')->user;
			$user->groups = $this->di->get('session')->getGroupRoles();
			return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
		}else{
			return array('status'=>'error', 'msg'=>"Not Logged In");
		}
	}
	

    /**
     * @SWG\Api(
     *   path="/Auth/Logout",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Logs the user out",
     *     notes="Tells the backend to remove access from your current auth token",
     *     type="void"
     *   )
     * )
     */
	public function logout(){
		if( $this->di->get('session')->logout() ){
			return array('status'=>'ok');
		}else{
			return array('status'=>'error', 'msg'=>"Logout Failed");
		}
	}


    /**
     * @SWG\Api(
     *   path="/Auth/AddUser",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Creates a new user",
     *     notes="Returns the authenticated user",
     *     type="void",
     *     authorizations={},
     *     @SWG\Parameter(
     *       name="email",
     *       description="email of the new user",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="email",
     *       description="password for the new user",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="email",
     *       description="display name for the new user",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     )
     *   )
     * )
     */
	public function addUser(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation->setFilters('password', 'trim');
		$validation->setFilters('email', 'trim');
		$validation->setFilters('display_name', 'trim');
		
		$args['email'] = $this->di->get('request')->get('email');
		$args['password']= $this->di->get('request')->get('password');
		$args['display_name']= $this->di->get('request')->get('display_name');
		$_user = new UserController();
		$result = $_user->action_addUser($args);
		return $result;
		
	}
	
		
    /**
     * @SWG\Api(
     *   path="/Auth/PasswordResetRequest",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Does a standard email password reset request",
     *     notes="Password reset request",
     *     type="void",
     *     authorizations={},
     *     @SWG\Parameter(
     *       name="email",
     *       description="email of the user requesting password reset for",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     )
     *   )
     * )
     */
	public function passwordResetRequest(){
		$validation = new \Phalcon\Validation();
		//Filter any extra space
		$validation->setFilters('email', 'trim');
		
		$args['email'] = $this->di->get('request')->get('email');
		
		$_user = new UserController();
		$result = $_user->action_passwordResetRequest($args);
		return $result;
		
	}
		
		
    /**
     * @SWG\Api(
     *   path="/Auth/PasswordReset",
     *   @SWG\Operation(
     *     method="POST",
     *     notes="This is the second half of the password reset.  Once the email has been sent with the reset token, you click on it and get a page requesting the new password.",
     *     summary="Handles the password reset",
     *     type="Void",
     *     authorizations={},
     *     @SWG\Parameter(
     *       name="email",
     *       description="email of the user requesting reset",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="activation_key",
     *       description="activation_key from password reset email",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="password",
     *       description="password for the user requesting reset",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="password_confirm",
     *       description="password confirmation entry",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *   )
     * )
     */
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

	
    /**
     * @SWG\Api(
     *   path="/Auth/ActivateUser",
     *   @SWG\Operation(
     *     method="POST",
     *     notes="When you sign up an email is set to verify the email address, this function handles the verification step",
     *     summary="Email confirmation check",
     *     type="void",
     *     authorizations={},
     *     @SWG\Parameter(
     *       name="email",
     *       description="email of the user requesting activation",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *     @SWG\Parameter(
     *       name="activation_key",
     *       description="activation_key from confirmation email",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     ),
     *   )
     * )
     */
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
		$user = new UserController();
		$result = $user->action_activateUser($args);
		return $result;
	}
	
	
    /**
     * @SWG\Api(
     *   path="/Auth/Group/Add",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Allows the user to create a new group and add them self to it",
     *     notes="Returns the authenticated user",
     *     type="Group",
     *     @SWG\Parameter(
     *       name="name",
     *       description="what you want to call the new group",
     *       required=true,
     *       type="string",
     *       paramType="query",
     *       allowMultiple=false
     *     )
     *   )
     * )
     */
	public function addGroup(){
		$name = $this->di->get('request')->get('name');
		$_user = $this->di->get('session')->user;
		
		$group = new \Auth\Controllers\GroupController();
		$result = $group->newGroup($name, $_user->id);
		return $result;
	}

	
    /**
     * @SWG\Api(
     *   path="/Auth/myGroups",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Get a list of all the groups i'm a member of",
     *     notes="Returns my group list",
     *     type="Group"
     *   )
     * )
     */
	public function myGroups(){
		$myGroups = array();
		$_groupRoles = $this->di->get('session')->getGroupRoles();
		foreach( $_groupRoles as $gr ){
			$myGroups[] = array( 'group'=>$gr->getGroup(), 'role'=> $gr->getRole() );
		}
		
		return $myGroups;
	}
	
	
    /**
     * @SWG\Api(
     *   path="/Auth/Group/{id}/Members",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Get a list of users in a given group",
     *     notes="Returns array of users in a given group",
     *     type="User",
     *     @SWG\Parameter(
     *       name="id",
     *       description="id of the group we want memberships for",
     *       required=true,
     *       type="string",
     *       paramType="path",
     *       allowMultiple=false
     *     )
     *   )
     * )
     */
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

	
    /**
     * @SWG\Api(
     *   path="/Auth/Role",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Get the roles for the current group, access permitting",
     *     notes="Returns the list of roles",
     *     type="Role"
     *   )
     * )
     */
	public function getRole(){
		$name = $this->di->get('request')->get('name');
		$group_id = $this->di->get('request')->get('group_id');
		if( $this->di->get('session')->can('manage_group',$group_id) ){
			$roleContoller = new RoleController();
			$result = $roleContoller->addRole($name,$group_id);
		}else{
			return array('status'=>'error','message'=>'Permission Denied');
		}
		
		$group = new GroupController();
		$result = $group->newGroup($name, $_user->id);
		return $result;
	}
	
}