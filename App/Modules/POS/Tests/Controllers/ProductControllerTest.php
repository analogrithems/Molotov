<?php
namespace Molotov\Modules\POS\Tests\Controllers;

use Molotov\Modules\POS\Controllers\ProductController;
use Molotov\Modules\POS\Models\Product;

class ProductControllerTest  extends \UnitTestCase
{

    public $user;
    
    public function setUp(\Phalcon\DiInterface $di = NULL, \Phalcon\Config $config = NULL)
    {
		parent::setUp();
    	$db = \Phalcon\DI::getDefault()->get('db');
	    $this->user = $this->createUser();
    }
    
	public function testSave(){
		include(__DIR__.'/../Mock/ProductMockModel.php');
		
		//cleanup test data
		$this->clearProducts();
		
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		
		//Save product type food
		$_REQUEST = array_merge(array('token'=>$this->token),$new_food);
		$productController = new ProductController();
		$response = $productController->save();
		$result = json_decode($response->getContent(),1);
		$this->assertEquals(1, $result['status'],'Could not find products:'.print_r($result,1));
		//make sure product structure looks right
		$compare_fields = array('name','description','short_description','special_instructions', 'currency', 'language', 'status', 'product_type');
		foreach($compare_fields as $f){
			$this->assertEquals($new_food[$f], $result['result'][$f], "Failed to set field {$f}:".print_r($result,1));			
		}
		//this is a root roduct, make sure the product_id & source get set right
		$this->assertEquals($result['result']['id'], $result['result']['product_id'], "Failed to auto set field product_id:".print_r($result,1));
		$this->assertEquals($result['result']['id'], $result['result']['source_id'], "Failed to auto set field product_id:".print_r($result,1));
		
		//make sure the fee, meta, taxonomy and variance counts match
		$field_count = array('fees','taxonomy','meta','variance');
		foreach($field_count as $f){
			$this->assertEquals(count($new_food[$f]), count($result['result'][$f]), "{$f} count is wrong:".print_r($result['result'][$f],1));
		}
		//search by users
		$this->logout();
		
		return $result['result'];
	}
	
	/**
	 * @depends testSave
	 */
	public function testSearch( $save ){
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		
		//search by names
		$_REQUEST = array('token'=>$this->token,'name'=>$save['name'],'product_type'=>$save['product_type'],'company_id'=>$this->testUser['company_id']);
		$productController = new ProductController();
		$response = $productController->search();
		$result = json_decode($response->getContent(),1);
		$this->assertEquals(1, $result['status'],'Could not find products:'.print_r($result,1));
		$this->assertGreaterThanOrEqual(1, $result['total'],'Could not find products:'.print_r($result,1));

		//search by users
		$this->logout();
		return $result['results'][0];
	}

    /**
     * @depends testSearch
     */
	public function testView( $search_result_0){
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		
		//Fetch Single Record
		$_REQUEST = array('token'=>$this->token);
		$productController = new ProductController();
		$response = $productController->view($search_result_0['id']);
		$result = json_decode($response->getContent(),1);
		$this->assertEquals(1, $result['status'],'Could not view product:'.print_r($result,1));
		$this->assertContains($search_result_0['name'], $result['result']['name'],'Could not view product:'.print_r($result,1));
		//search by users
		$this->logout(); 
	}
	
    /**
     * @depends testSearch
     */
	public function testClone( $search_result_0){
		include(__DIR__.'/../Mock/ProductMockModel.php');
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		
		//Fetch Single Record
		$_REQUEST = array('token'=>$this->token,'id'=>$search_result_0['id']);
		$productController = new ProductController();
		$response = $productController->cloneProduct();
		$result = json_decode($response->getContent(),1);

		$this->assertEquals(1, $result['status'],'Could not clone product:'.print_r($result,1));
		//make sure product structure looks right
		$compare_fields = array('name','description','short_description','special_instructions', 'currency', 'language', 'status', 'product_type');
		foreach($compare_fields as $f){
			$this->assertEquals($new_food[$f], $result['result'][$f], "Failed to set field {$f}:".print_r($result,1));			
		}
		//this is a root roduct, make sure the product_id & source get set right
		$this->assertEquals($result['result']['id'], $result['result']['product_id'], "Failed to auto set field product_id:".print_r($result,1));
		$this->assertEquals($result['result']['id'], $result['result']['source_id'], "Failed to auto set field product_id:".print_r($result,1));
		
		//make sure the fee, meta, taxonomy and variance counts match
		$field_count = array('fees','taxonomy','meta','variance');
		foreach($field_count as $f){
			$this->assertEquals(count($new_food[$f]), count($result['result'][$f]), "{$f} count is wrong:".print_r($result['result'][$f],1));
		}
		//search by users
		$this->logout();
		
		return $result['result'];
	}
	
    /**
     * @depends testSearch
     */
	public function testAdopt( $product ){
		include(__DIR__.'/../Mock/ProductMockModel.php');
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		$this->changeCompany(216);
		
		//Fetch Single Record
		$_REQUEST = array('token'=>$this->token,'id'=>$product['id']);
		$productController = new ProductController();
		$response = $productController->adopt();
		$result = json_decode($response->getContent(),1);
		//echo "Adopted product looks like:".print_r($result,1)."\n";
		//die("Debug");
		$this->assertEquals(1, $result['status'],'Could not clone product:'.print_r($result,1));
		//make sure product structure looks right
		$compare_fields = array('name','description','short_description','special_instructions', 'currency', 'language', 'status', 'product_type');
		foreach($compare_fields as $f){
			$this->assertEquals($new_food[$f], $result['result'][$f], "Failed to set field {$f}:".print_r($result,1));			
		}
		//this is a root roduct, make sure the product_id & source get set right
		$this->assertGreaterThan($result['result']['product_id'], $result['result']['id'], "Id should be different then product_id now:".print_r($result,1));
		$this->assertEquals($result['result']['product_id'], $result['result']['source_id'], "source_id does not match product_id:".print_r($result,1));
		
		//make sure the fee, meta, taxonomy and variance counts match
		$field_count = array('fees','taxonomy','meta','variance');
		foreach($field_count as $f){
			$this->assertEquals(count($new_food[$f]), count($result['result'][$f]), "{$f} count is wrong:".print_r($result['result'][$f],1));
		}
		//search by users
		$this->logout();
		
		return $result['result'];
	}
	
    /**
     * @depends testAdopt
     */
	public function testTranslate( $product ){
		include(__DIR__.'/../Mock/ProductMockModel.php');
		$this->clearbadLogins();
		$result = $this->login($this->testUser['username'],$this->testUser['password']);
		$this->changeCompany(216);
		
		//Fetch Single Record
		$_REQUEST = array('token'=>$this->token,'id'=>$product['id'],'language'=>'ja');
		$new_food['language'] = 'ja';
		$productController = new ProductController();
		$response = $productController->translate();
		$result = json_decode($response->getContent(),1);
		//echo "Adopted product looks like:".print_r($product,1)."\n";
		//echo "Translated product looks like:".print_r($result,1)."\n";
		//die("Debug");
		$this->assertEquals(1, $result['status'],'Could not clone product:'.print_r($result,1));
		//make sure product structure looks right
		$compare_fields = array('name','description','short_description','special_instructions', 'currency', 'language', 'status', 'product_type');
		foreach($compare_fields as $f){
			$this->assertEquals($new_food[$f], $result['result'][$f], "Failed to set field {$f}:".print_r($result,1));			
		}
		//this is a root roduct, make sure the product_id & source get set right
		$this->assertGreaterThan($result['result']['product_id'], $result['result']['id'], "Failed to auto set field product_id:".print_r($result,1));
		$this->assertGreaterThan($result['result']['source_id'], $result['result']['id'], "Failed to auto set field product_id:".print_r($result,1));
		$this->assertEquals($product['id'], $result['result']['source_id'], "Failed to auto set field product_id:".print_r($result,1));
		
		//make sure the fee, meta, taxonomy and variance counts match
		$field_count = array('fees','taxonomy','meta','variance');
		foreach($field_count as $f){
			$this->assertEquals(count($new_food[$f]), count($result['result'][$f]), "{$f} count is wrong:".print_r($result['result'][$f],1));
		}
		//search by users
		$this->logout();
		
		return $result['result'];
	}
	
	private function clearProducts(){
		include(__DIR__.'/../Mock/ProductMockModel.php');
		
		$db = \Phalcon\DI::getDefault()->get('db');
		$sql = array();
		$sql[] = "TRUNCATE TABLE product";
		$sql[] = "TRUNCATE TABLE product_fee";
		$sql[] = "TRUNCATE TABLE product_fee_translation";
		$sql[] = "TRUNCATE TABLE product_variance";
		$sql[] = "TRUNCATE TABLE product_variance_translation";
		$sql[] = "TRUNCATE TABLE product_variance_item";
		$sql[] = "TRUNCATE TABLE product_variance_item_translation";
		$sql[] = "TRUNCATE TABLE product_meta";
		$sql[] = "TRUNCATE TABLE product_taxonomy";
		$sql[] = "TRUNCATE TABLE product_taxonomy_relationships";
		
		foreach($sql as $s){
			$db->execute( $s );	
		}
		
	}

	protected function tearDown()
	{
		$this->deleteUser();
		parent::tearDown();     
	}
}
