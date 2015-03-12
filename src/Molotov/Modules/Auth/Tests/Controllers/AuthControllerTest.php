<?php
namespace Molotov\Modules\Auth\Tests\Controllers;
use Molotov\Core\Tests\UnitTestCase;

class AuthControllerTest extends UnitTestCase{
	
    public function setUp(\Phalcon\DiInterface $di = NULL, \Phalcon\Config $config = NULL)
    {
		parent::setUp();
	    
    }
	
	public function testLogin(){
		$this->user = $this->createUser();
		$u = $this->login($this->user['email'], $this->user['password']);
		$this->deleteUser($u['id']);
		
	}
}