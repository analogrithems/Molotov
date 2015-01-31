<?php
namespace Molotov\Core\Tests;

use Molotov\Modules\Auth\Models\User;

abstract class UnitTestCase extends \Phalcon\Test\UnitTestCase {

    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;
    
    /**
	 * @var $token
	 */
	public $token;
	
	/**
	 * @var $db
	 */
	public $db;

    /**
     * @var bool
     */
    private $_loaded = false;
    
    
    /**
	 * create a temporary user for unit test
	 */
    public function createUser(){
	   $email = 'tempUser@'.uniqid().'.org';
	   $password = '1qaz@WSX';
        $authController = new \Molotov\Modules\Auth\Controllers\AuthController();
        $_REQUEST = array(
        	'email'=> $email,
        	'password'=> $password,
        	'display_name'=>'temp user'
        );
	    $_user = $_REQUEST;
	    
        $response = $authController->addUser();
		$this->assertEquals('ok',$response['status'],'Add user failed to create a temp user:'.print_r($response,1));
	    //get user_id
	    $result = $this->db->query("SELECT * FROM user WHERE email = :email",array('email'=>$_user['email']));
	    $user = $result->fetch();

	    //accept invite
		$pdoResult = $this->db->query("SELECT * FROM emailactivations WHERE type = 'signup' AND used = 0 and user_id = :user_id", array('user_id' => $user['id']));
		foreach( $pdoResult->fetchAll() as $row){
			$_REQUEST = array(
				'activation_key'=>$row['activation_key'],
				'email'=>$user['email']
			);
			$ac = new \Molotov\Modules\Auth\Controllers\AuthController();
			$r = $ac->ActivateUser();
			$this->assertEquals('ok',$r['status'],'Add user failed to activate a temp user:'.print_r($r,1));
		}
	    
	    return $_user;
    }
    
	public function login($email, $password){
        $authController = new \Molotov\Modules\Auth\Controllers\AuthController();
        $_REQUEST = array(
        	'email'=> $email,
        	'password'=> $password
        );
        $response = $authController->login();
		$this->assertEquals('ok',$response['status'],'Failed to login user:'.print_r($response,1));
		$this->assertGreaterThan(0,$response['user']['id'],'Invalid user id');
		//todo check for token & set it globally
        return $response['user'];
	}
	
	public function deleteUser( $user_id ){
		$user = User::findFirst(array(
			"id = :id:",
			"bind" => array(
				"id" => $user_id
			)
		));
		if($user){
			$this->assertNotEquals(false,$user->delete(),'Failed to delete user:'.$user_id);
		}
	}

    public function setUp() {

        // Load any additional services that might be required during testing
        $di = \Phalcon\DI::getDefault();
        $this->db = $di->get('db');

        // get any DI components here, if you have a config, be sure to pass it to the parent

        parent::setUp($di);

        $this->_loaded = true;
    }

    /**
     * Check if the test case is setup properly
     * @throws \PHPUnit_Framework_IncompleteTestError;
     */
    public function __destruct() {
        if(!$this->_loaded) {
            throw new \PHPUnit_Framework_IncompleteTestError('Please run parent::setUp().');
        }
    }
}
