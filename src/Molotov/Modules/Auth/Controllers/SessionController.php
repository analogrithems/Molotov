<?php
namespace Molotov\Modules\Auth\Controllers;

use Molotov\Modules\Auth\Models\Session;
use Molotov\Modules\Auth\Models\UserGroups;
use Molotov\Modules\Auth\Models\User;
use Molotov\Modules\Auth\Models\Group;
use Molotov\Modules\Auth\Models\Role;

/*
 * Standard Session Controller responsable for handeling user login requests
 */
  
class SessionController {
	protected static $instance = null;
	public $session, $user, $groupRoles, $di;

	protected function __construct(){}
	protected function __clone(){}

	public static function instance($di=null){
		if(!isset(static::$instance)){
			$inst = new static;
			$inst->init();
			$inst->di = \Phalcon\DI::getDefault();
			static::$instance = $inst;
		}
		return static::$instance;
	}

	/*
	* init function here will check for token and try to unserialize the current session.  If no
	* session exists it will start one
	*/
	private function init(){
		if( !isset($_COOKIE[AUTH_COOKIE_NAME]) ){
			$session_id = uniqid(sha1(rand()),true);
			$request = new \Phalcon\Http\Request();
			setcookie(
				AUTH_COOKIE_NAME,
				$session_id,
				AUTH_COOKIE_EXPIRE,
				AUTH_COOKIE_PATH,
				AUTH_COOKIE_DOMAIN,
				AUTH_COOKIE_SECURE
			);
			
			$this->session = new Session();
			$this->session->session = array();
			$this->session->user_id = 0;
			$this->session->token = $session_id;
			$this->session->created = date('Y-m-d H:i:s');
			$this->session->ip = $request->getClientAddress();
			$this->session->save();
		}else{
			//we have a cookie unserialize our session
			$this->session = Session::findFirst(
				array(
					"token = :token:",
					"bind" => array(
						'token' => $_COOKIE[AUTH_COOKIE_NAME]
					)
				)
			);
			if(!$this->session){
				//session invalid, rebuild
				unset($_COOKIE[AUTH_COOKIE_NAME]);
				$this->init();
			}
			
			if( $this->session->user_id > 0 ){
				$this->user = User::findFirst(
					array(
						"id = :id:",
						"bind" => array(
							'id' => $this->session->user_id
						)
					)
				);
				
			}
		}
	}
	
	public function login($login, $pass){
	
		
		$user = User::findFirst(
			array(
				"(display_name = :display_name: OR email = :email:) AND enabled = 1",
				"bind" => array(
					'email' => $login,
					'display_name' => $login,
				)
			)
		);
		
		if( $user && $this->di->get('security')->checkHash($pass,$user->password)){
			//user was authenticated, update their session if they have one to prevent them
			//having to auth again later
			$this->session->user_id = $user->id;
			$this->session->save();
			$this->user = $user;
			return true;
		}else{
			$result = $this->di->get('eventsManager')->fire( 'session:login', $this, array('login'=>$login,'pass'=>$pass) );
			if($result && is_a($result,'Molotov\Auth\Models\User') ){
				$this->session->user_id = $result->id;
				$this->session->save();
				$this->user = $result;
				return true;
			}else{
				return false;
			}
		}
		
		
		return false;
	}
	
	public function sessionCheck(){
		if( $this->session->user_id > 0 ) return true;
		else return false;
	}

	/*
	* logout is simple, send a new session token, this disconnects the user from their previous session
	*/	
	public function logout(){
		$this->di->get('eventsManager')->fire( 'session:logout', $this);
		unset($_COOKIE[AUTH_COOKIE_NAME]);
		$this->init();
		return true;
	}
	
	/*
	*  can is a a simple acl check function that checks if the curent user has a given 
	*  capability for a specific group.
	*
	* @param string name of a specific capability
	* @param (Group | int) $group either a Group object or the group id
	* @return bool
	*/
	public function can( $capability, $group ){
		$groupRoles = $this->getGroupRoles();
		foreach( $groupRoles as $gr ){
			if(is_numeric($group) && $group == $gr->getGroup()->id ){
				foreach( $gr->getRole()->getCapabilites() as $cap ){
					if( $cap->capability == $capability) return true;
				}
			}elseif( is_object($group) && property_exists( $group, 'id') && $group->id == $gr->getGroup()->id ){
				foreach( $gr->getRole()->getCapabilites() as $cap ){
					if( $cap->capability == $capability) return true;
				}				
			}
		}
		return false;
	}
	/*
	* getGroupRoles - Go fetch all the roles and groups for the current session
	*/
	public function getGroupRoles(){
		if($this->session->user_id > 0)	{
			if( $this->groupRoles ){
				return $this->groupRoles;
			}else{
				$groupRoles = UserGroups::find(array(
					" user_id = :user_id: ".
					'bind'=>array(
						'user_id'=>$this->session->user_id
					)
				));
				foreach( $groupRoles as $gr ){
					$group = Group::findFirst($gr->group_id);
					$group->role = Role::findFirst($gr->role_id);
					$group->role->group_id = $gr->group_id;
					$group->role->capabilities = $group->role->getCapabilites();
					$this->groupRoles[] = $group;
					unset($tmp);
				}
				return $this->groupRoles;
			}				
		}else{
			return false;
		}
	}
}	
?>