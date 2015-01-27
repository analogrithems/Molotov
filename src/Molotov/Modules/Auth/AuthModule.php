<?php

namespace Molotov\Modules\Auth;

use Molotov\Core\Abstracts\Module;

class AuthModule extends Module
{
	protected $routes = array(
		'/Auth/Login' => 'login',
		'/Auth/Logout' => 'logout',
		'/Auth/Capabilities'=>'getCapabilities',
		'/Auth/Role' => 'getRole',
		'/Auth/Group/{id}/Members' => 'getMembers',
		'/Auth/myGroups' => 'myGroups',
		'/Auth/Group' => 'getGroup',
		'/Auth/ActivateUser' => 'ActivateUser',
		'/Auth/PasswordReset' => 'PasswordReset',
		'/Auth/SessionCheck' => 'SessionCheck',
		'/Auth/AddUser' => 'addUser',
		'/Auth/PasswordResetRequest' => 'passwordResetRequest',
		
		
	);
	
	protected $controller = 'Molotov\Modules\Auth\Controllers\AuthController';

	
	protected $services = array(
		'session' => array('Molotov\Modules\Auth\Controllers\SessionController', 'instance')
	);
	
	public function onConstruct(){

		$this->di->get('pages')->addPage(
			array(
				'alias'=>'dashboard',
				'path'=>'/admin',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Pages/',
				'before_content'=>function($pages){
					$before_content_widgets = '';
					$before_content_widgets .= $pages->getWidget('login');
					$before_content_widgets .= $pages->getWidget('signup');
					$before_content_widgets .= $pages->getWidget('signupActivation');
					$before_content_widgets .= $pages->getWidget('passwordReset');
					$before_content_widgets .= $pages->getWidget('passwordResetRequest');
					return $before_content_widgets;
				}
			)
		);
		
		$this->di->get('pages')->addWidget(
			array(
				'alias'=>'login',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Widgets/'
			)
		);
		
		$this->di->get('pages')->addWidget(
			array(
				'alias'=>'signup',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Widgets/'
			)
		);
		
		$this->di->get('pages')->addWidget(
			array(
				'alias'=>'signupActivation',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Widgets/'
			)
		);
		
		$this->di->get('pages')->addWidget(
			array(
				'alias'=>'passwordReset',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Widgets/'
			)
		);
		
		$this->di->get('pages')->addWidget(
			array(
				'alias'=>'passwordResetRequest',
				'viewDir'=>AUTH_MODULE_DIR.'/Web/Widgets/'
			)
		);

	}	
}