<?php

define("AUTH_MODULE_DIR",MODULES_DIR.'/Auth');
define("AUTH_COOKIE_NAME",'JHANCOCK');
define("AUTH_COOKIE_PATH",'/');
define("AUTH_COOKIE_DOMAIN",'');
define("AUTH_COOKIE_EXPIRE",0);
define("AUTH_COOKIE_SECURE",0);

define("AUTH_FROM_EMAIL","noreply@example.net");


require_once(AUTH_MODULE_DIR . '/vendor/autoload.php');

/*
$app->before(function() use ($app) {
	\Auth\Controllers\Session::instance()
});
*/

//our user session
$di->setShared('session', function() use($di){
	return  Molotov\Modules\Auth\Controllers\SessionController::instance($di);
});


//Session Login event hook
$di->get('eventsManager')->attach('session:login', function($event, $component, $data) {
	
/*
	$data contains the $data['user'] && $data['pass'] that was attempted to login with
	if you want to try an external auth system like LDAP
	test the password and return an object of type \Auth\Models\User
	 If a user object does not yet exists, then be sure to create one and return that.
	if the user does exists, just use the external source to test the password
*/
});

function is_json($str){
    try{
	if(is_array($str)) return false;
        $jObject = json_decode($str,1);
    }catch(Exception $e){
        return false;
    }
    if(is_object($jObject) || is_array($jObject)){
		return true;
    }else{
		return false;
    }
}

//View
$app->map('/Auth',function() use ($app,$di){
	$viewPath = AUTH_MODULE_DIR.'/Views/Web/Auth/';
	$view 	=  $di->get('view');
	$assets =  $di->get('assets');
	$assets->addCss('css/semantic.min.css');
	$assets->addJs('js/jquery-1.10.2.min.js');
	$assets->addJs('js/knockout.js');
	$assets->addJs('js/semantic.min.js');
	$assets->addJs('js/ar.js');
	$assets->addJs('js/modules/utils.js');
	$assets->addJs('js/modules/api.js');
	$assets->addJs('js/modules/pubsub.js');
	$assets->addJs('js/modules/dom.js');
	$assets->addJs('js/modules/model.js');
	$assets->addJs('js/modules/model_manager.js');
	$assets->addJs('js/modules/router.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/login.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/passwordReset.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/passwordResetRequest.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/dashboard.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/group.js');
	$view->setViewsDir($viewPath);
	echo $view->render('index',array('assets'=>$assets));

})->via(array('GET','POST'));

$app->map('/Signup',function() use ($app,$di){
	$viewPath = AUTH_MODULE_DIR.'/Views/Web/Auth/';
	$view =  $di->get('view');
	$assets =  $di->get('assets');
	$assets->addCss('css/semantic.min.css');
	$assets->addJs('js/knockout.js');
	$assets->addJs('js/ar.js');
	$assets->addJs('js/modules/utils.js');
	$assets->addJs('js/modules/api.js');
	$assets->addJs('js/modules/pubsub.js');
	$assets->addJs('js/modules/dom.js');
	$assets->addJs('js/modules/model.js');
	$assets->addJs('js/modules/router.js');
	$assets->addJs('src/Molotov/Modules/Auth/Views/Web/Auth/js/signup.js');
	$view->setViewsDir($viewPath);
	echo $view->render('index',array('assets'=>$assets));

})->via(array('GET','POST'));

//API Calls
$app->map('/api/Auth/Login',function() use ($app, $di){
	
	$email 		= strtolower($app->request->get('email'));
	$password 	= $app->request->get('password');

	if( $di->get('session')->login($email,$password) ){
		$user = $di->get('session')->user;
		$user->groups = $di->get('session')->getGroupRoles();
		return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
	}else{
		return array('status'=>'error', 'msg'=>"Username or password incorrect");
	}
})->via(array('GET','POST'));

$app->map('/api/Auth/SessionCheck',function() use ($app,$di){
	if( $di->get('session')->sessionCheck() ){
		$user = $di->get('session')->user;
		$user->groups = $di->get('session')->getGroupRoles();
		return array('status'=>'ok', 'user'=>$user->serialize(array('id','display_name','email','groups')));
	}else{
		return array('status'=>'error', 'msg'=>"Not Logged In");
	}
})->via(array('GET','POST'));

$app->map('/api/Auth/Logout',function() use ($app,$di){

	if( $di->get('session')->logout() ){
		return array('status'=>'ok');
	}else{
		return array('status'=>'error', 'msg'=>"Logout Failed");
	}
})->via(array('GET','POST'));

$app->map('/api/Auth/AddUser',function() use ($app){
	$validation = new Phalcon\Validation();
	//Filter any extra space
	$validation->setFilters('password', 'trim');
	$validation->setFilters('email', 'trim');
	$validation->setFilters('display_name', 'trim');
	
	$args['email'] = $app->request->get('email');
	$args['password']= $app->request->get('password');
	$args['display_name']= $app->request->get('display_name');
	$_user = new Molotov\Modules\Auth\Controllers\UserController();
	$result = $_user->action_addUser($args);
	return $result;
	
})->via(array('GET','POST'));

$app->map('/api/Auth/PasswordResetRequest',function() use ($app){
	$validation = new Phalcon\Validation();
	//Filter any extra space
	$validation->setFilters('email', 'trim');
	
	$args['email'] = $app->request->get('email');
	
	$_user = new Molotov\Modules\Auth\Controllers\UserController();
	$result = $_user->action_passwordResetRequest($args);
	return $result;
	
})->via(array('GET','POST'));

$app->map('/api/Auth/PasswordReset',function() use ($app){
	$validation = new Phalcon\Validation();
	//Filter any extra space
	$validation->setFilters('email', 'trim');
	$validation->setFilters('password', 'trim');
	$validation->setFilters('password_confirm', 'trim');
	$validation->setFilters('activation_key', 'trim');
	
	if( $app->request->get('password') == $app->request->get('password_confirm')){
		$args['email'] = $app->request->get('email');
		$args['password']= $app->request->get('password');
		$args['activation_key']= $app->request->get('activation_key');
		$_user = new Molotov\Modules\Auth\Controllers\UserController();
		$result = $_user->action_passwordReset($args);
		return $result;
	}else{
		return array('status'=>'error','msg'=>"Passwords do not match");
	}
	
})->via(array('GET','POST'));

$app->map('/api/Auth/SignupActivation',function() use ($app){
	$validation = new Phalcon\Validation();
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
	
	$args['email'] = $app->request->get('email');
	$args['activation_key']= $app->request->get('activation_key');
	$user = new Molotov\Modules\Auth\Controllers\UserController();
	$result = $user->action_activateUser($args);
	return $result;
	
})->via(array('GET','POST'));

$app->map('/api/Auth/Group',function() use ($app,$di){
	$name = $app->request->get('name');
	$_user = $di->get('session')->user;
	
	$group = new \Auth\Controllers\GroupController();
	$result = $group->newGroup($name, $_user->id);
	return $result;
	
})->via(array('POST'));

$app->map('/api/Auth/myGroups',function() use ($app,$di){
	$myGroups = array();
	$_groupRoles = $di->get('session')->getGroupRoles();
	foreach( $_groupRoles as $gr ){
		$myGroups[] = array( 'group'=>$gr->getGroup(), 'role'=> $gr->getRole() );
	}
	
	return $myGroups;
	
})->via(array('GET'));

$app->map('/api/Auth/Group/{id}/Members',function($id) use ($app){
	$members = array();
	$groupController = new Molotov\Modules\Auth\Controllers\GroupController();
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
	
})->via(array('GET'));

$app->map('/api/Auth/Role',function() use ($app,$di){
	$name = $app->request->get('name');
	$group_id = $app->request->get('group_id');
	if( $di->get('session')->can('manage_group',$group_id) ){
		$roleContoller = new Molotov\Modules\Auth\Controllers\RoleController();
		$result - $roleContoller->addRole($name,$group_id);
	}else{
		return array('status'=>'error','message'=>'Permission Denied');
	}
	
	$group = new Molotov\Modules\Auth\Controllers\GroupController();
	$result = $group->newGroup($name, $_user->id);
	return $result;
	
})->via(array('POST'));


$app->map('/api/Auth/Capabilities',function() use ($app){
	$capabilityController = new Molotov\Modules\Auth\Controllers\CapabilityController();
	$caps = $capabilityController->getCapabilities();
	$result = array();
	foreach($caps as $cap){
		$result[] = array( 'id'=>$cap->id, 'name'=>$cap->capability );
	}
	return $result;
	
})->via(array('GET'));

