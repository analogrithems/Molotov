<?php
namespace Auth\Controllers;
/*
 * The User Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class User extends BaseController{

	public function action_addUser( $args ){
		$passwordHasher = new \Hautelook\Phpass\PasswordHash(8, true);
		$_user = new \Auth\Models\User();
		$_user->email = strtolower($args['email']);
		$_user->display_name = $args['email'];
		$_user->password = $passwordHasher->HashPassword($args['password']);
		$_user->created = date('Y-m-d H:i:s');
		$_user->enabled = 0;
		if($_user->save() && $_user->id > 0){
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
		$activation = \Auth\Models\EmailActivations::findFirst(array(
			"used = 0 AND activation_key = :activation_key:",
			"bind" => array(
				"activation_key" => $args['activation_key']
			)
		));
		if($activation){
			$user = \Auth\Models\User::findFirst(
				array(
					"enabled=0 AND id= :id: AND type = 'signup'", 
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
		return array("status"=>'error',"message"=>"Activation failed");

	}
	
	public function sendActivationEmail( $user ){
		$activation = new \Auth\Models\EmailActivations();
		$activation->user_id = $user->id;
		$activation->type = 'signup';
		if($activation->save()){

			//Email Template
		    $view = new \Phalcon\Mvc\View\Simple();			
		    $view->setViewsDir(AUTH_MODULE_DIR.'/Views/');
		    $email = $view->render(
		    	'Email/addUser',
		    	array(
		    		'user'=>$_user,
		    		'activation'=>$activation,
		    		'config'=>$this->di->get('config')
		    	)
		    );
			$message = \Swift_Message::newInstance()
				->setSubject('Account Verification Request')
				->setFrom(array(AUTH_FROM_EMAIL))
				->setTo(array($user->email=>$user->display_name))
				//TODO get template returned here as email template
				->setBody(strip_tags($mail))
				->addPart($email, 'text/html');
			return true;
		}
		return false;
	}
	
	public function action_passwordResetRequest( $args ){
		
		$user = \Auth\Models\User::findFirst(
			array(
				"email = :email:  AND enabled = 1",
				"bind" => array(
					'email' => $args['email']
				)
			)
		);
		if(!$user) return array('status'=>'ok','msg'=>"If a matching account exists then a password reset request will be sent to the email");
		
		$activation = new \Auth\Models\EmailActivations();
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
			$message = \Swift_Message::newInstance()
				->setSubject('Password Reset Request')
				->setFrom(array(AUTH_FROM_EMAIL))
				->setTo(array($user->email=>$user->display_name))
				//TODO get template returned here as email template
				->setBody(strip_tags($mail))
				->addPart($email, 'text/html');
			return true;
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
		$activation = \Auth\Models\EmailActivations::findFirst(array(
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