<?php

define("AUTH_MODULE_DIR",MODULES_DIR.'/Auth');
define("AUTH_COOKIE_NAME",'MOLOTOV');
define("AUTH_COOKIE_PATH",'/');
define("AUTH_COOKIE_DOMAIN",'');
define("AUTH_COOKIE_EXPIRE",0);
define("AUTH_COOKIE_SECURE",0);

define("AUTH_FROM_EMAIL","noreply@example.net");


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





