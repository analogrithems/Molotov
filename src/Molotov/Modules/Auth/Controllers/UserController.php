<?php
namespace Molotov\Modules\Auth\Controllers;
/*
 * The User Controller
 */
 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Auth\Models\User;
use Molotov\Modules\Auth\Models\Group;
use Molotov\Modules\Auth\Models\UserGroups;
use Molotov\Modules\Auth\Models\EmailActivations;


class UserController extends BaseController{

	public function action_addUser( $args ){
		//check if company exists or create it, if it exists make sure we have access to add to it
		$session = $this->di->get('session');
		$group = Group::findFirst(array(
			"conditions"=>"name = :name:",
			"bind"=>array(
				"name"=>$args['group']
			)
		));
		if($group){
			die("Group:".print_r($group->serialize(),1)."\nGroup id:".print_r($session->user,1)."\nArgs:".print_r($args,1) );
			if($session->user->group_id != $group->id || $session->can('add_user')){
				return array('status'=>'error', 'message'=>"Permission denied");
			}
		}else{
			$group = new Group();
			$group->name = $args['group'];
			$group->save();
		}
		
		$_user = new User();
		$_user->email = strtolower($args['email']);
		$_user->display_name = $args['display_name'];
		$_user->password = $this->security->hash($args['password']);
		$_user->created = date('Y-m-d H:i:s');
		$_user->group_id = $group->id;
		$_user->enabled = 0;
		if($_user->save() && $_user->id > 0){
			
			if($group->id){
				$userGroup = new UserGroups();
				$userGroup->user_id = $_user->id;
				$userGroup->group_id = $group->id;
				$userGroup->setRole('administrator');
				$userGroup->save();
			}
			//send activation email
			if( $this->sendActivationEmail($_user)) return array('status'=>'ok','message'=>"User created, please check email for account verification");
			else return array('status'=>'error', 'message'=>"Email activation failed");
		}else{
			$results = array('status'=>'error','messages'=>array() );
			foreach ($_user->getMessages() as $message) {
				$results['messages'][] = $message->getMessage();
			}
			return $results;
		}
	}
	
	public function action_activateUser( $args ){
		$activation = EmailActivations::findFirst(array(
			"used = 0 AND activation_key = :activation_key: AND type = 'signup'",
			"bind" => array(
				"activation_key" => $args['activation_key']
			)
		));
		if($activation){
			$user = User::findFirst(
				array(
					"enabled=0 AND id= :id:", 
					"bind"=>
						array(
							"id"=>$activation->user_id
						)
				)
			);
			if( strtolower($args['email']) == strtolower($user->email) ){
				$activation->used = 1;
				$user->enabled = 1;
				if( $user->save() && $activation->save() ){
					return array('status'=>'ok','message'=>"Your account is activated");
				}

			}
		}
		return array("status"=>'error',"message"=>"Activation failed {$args['email']} != {$user->email}".print_r($activation,1));

	}
	
	public function sendActivationEmail( $user ){
		$activation = new EmailActivations();
		$activation->user_id = $user->id;
		$activation->type = 'signup';
		if($activation->save()){

			//Email Template
		    $view = new \Phalcon\Mvc\View\Simple();
			$view->registerEngines(array(
			  ".view" => "Phalcon\Mvc\View\Engine\Volt",
			));
		    $view->setViewsDir(AUTH_MODULE_DIR.'/Views/');
		    $view->setDI(\Phalcon\DI::getDefault());
		    $email = $view->render(
		    	'Email/addUser',
		    	array(
		    		'user'=>$user,
		    		'activation'=> $this->di->get('config')['site_url'].'/Signup/#SignupActivation/'.$activation->activation_key.'/'.$user->email
		    	)
		    );
		    return \Molotov\Core\Lib\Email::email($user->email,'Account Verification Request',$email,'From: '.AUTH_FROM_EMAIL);
		}
		return false;
	}
	
	public function action_passwordResetRequest( $args ){
		
		$user = User::findFirst(
			array(
				"email = :email:  AND enabled = 1",
				"bind" => array(
					'email' => $args['email']
				)
			)
		);
		if(!$user) return array('status'=>'ok','msg'=>"If a matching account exists then a password reset request will be sent to the email");
		
		$activation = new EmailActivations();
		$activation->user_id = $user->id;
		$activation->type = 'passwordreset';
		if($activation->save()){

			//Email Template
			$view = new \Phalcon\Mvc\View\Simple();			
			$view->setViewsDir(AUTH_MODULE_DIR.'/Views/');
			$email = $view->render(
			    'Email/passwordReset',
			    array(
				    'user'=>$_user,
				    'activation'=>$activation,
				    'config'=>$this->di->get('config')
			    )
			);
			$transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
			$mailer = \Swift_Mailer::newInstance($transport);
			$message = \Swift_Message::newInstance()
				->setSubject('Password Reset Request')
				->setFrom(array(AUTH_FROM_EMAIL))
				->setTo(array($user->email=>$user->display_name))
				//TODO get template returned here as email template
				->setBody(strip_tags($mail))
				->addPart($email, 'text/html');
				
			$result = $mailer->send($message);
			return $result;
		}
		return false;
	}
	
	public function action_passwordReset( $args ){
		$user = \Auth\Models\User::findFirst(
			array(
				"email = :email:  AND enabled = 1",
				"bind" => array(
					'email' => $args['email']
				)
			)
		);
		$activation = EmailActivations::findFirst(array(
			"used = 0 AND activation_key = :activation_key: AND user_id = :user_id: AND type = 'passwordreset'",
			"bind" => array(
				"activation_key" => $args['activation_key'],
				"user_id" => $args['email']
			)
		));
		if($activation){
			$result = $this->di->get('eventsManager')->fire( 'user:passwordReset', $this, array('user'=>$user,'pass'=>$args['password']) );
			$activation->used = 1;
			//hash new password
			$passwordHasher = new \Hautelook\Phpass\PasswordHash(8, true);
			$user->password = $passwordHasher->HashPassword($args['password']);
			if( $user->save() && $activation->save() ){
				return array('status'=>'ok','message'=>"Your Password Has Been Reset");
			}
		}
		return array("status"=>'error',"message"=>"Activation failed");
	}
}