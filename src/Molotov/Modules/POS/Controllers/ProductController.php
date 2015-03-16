<?php
namespace Molotov\Modules\POS\Controllers;

use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\POS\Models\Product;

class ProductController extends Controller {

	public function search(){
		$products = array('status'=>1);
		$args = $this->di->get('request')->get();
 
		$_products = Product::search($args);
		$products['total'] = $_products['total'];
		foreach($_products['results'] as $_product){
			$products['results'][] = $_product->out(array(
				'id',
				'name',
				'description',
				'short_description',
				'language',
				'currency',
				'fees',
				'variance',
				'meta',
				'taxonomy',
				'product_id',
				'status',
				'workflow',
				'product_type',
				'created'
			));
		}
		return $this->jsonResponse($products);
	}
	
	public function save(){
		$auth = $this->di->get('auth');
		$args = $this->di->get('request')->get();
		
		if(!$auth->can('edit_product')){
			return $this->jsonResponse(array(
				'status'=>-99,
				'msg'=>'Permission Denied'
			));
		}
		
		//check ownership
		if( isset($args['id']) && $args['id'] > 0 ){
			if($auth->user->company_id != Product::getCompanyId($args['id'])){
				return $this->jsonResponse(array(
					'status'=>-99,
					'msg'=>'Permission Denied; not owned.'
				));
			}
		}
		
		//make sure product_type is set
		if ( !isset($args['product_type']) || empty($args['product_type']) ){
			return $this->jsonResponse(array(
				'status'=>-3,
				'msg'=>'Product type required.'
			));
		}
		
		$product = new Product($args);
		$product->save();
		$result = array('status'=>1);
		$result['result'] = $product->out();
		return $this->jsonResponse($result);
	}
	
	
	public function delete(){
		$auth = $this->di->get('auth');
		$args = $this->di->get('request')->get();
		if(!$auth->can('delete_product')){
			return $this->jsonResponse(array(
				'status'=>-99,
				'msg'=>'Permission Denied'
			));
		}
		
		//check ownership
		if( isset($args['id']) && $args['id'] > 0 ){
			if($auth->user->company_id != Product::getCompanyId($args['id'])){
				return $this->jsonResponse(array(
					'status'=>-99,
					'msg'=>'Permission Denied; not owned.'
				));
			}
		}
		
		
		if(isset($args['id']) && ($product = Product::fromId($args['id'])) ){
			$product->delete();
			return $this->jsonResponse(array('id'=>1,'msg'=>'Product Deleted'));
		}else{
			return $this->jsonResponse(array('id'=>-1,'msg'=>'No Product found'));
		}
		
	}
	
	public function view( $id ){
		$product = Product::fromId($id);
		if($product && is_object($product) ){
			$result = array('status'=>1,'result'=>$product->out());
		}else{
			$id = (int)$id;
			$result = array('status'=>-1,'msg'=>"Invalid product_id given: {$id}");
		}
		return $this->jsonResponse($result);
	}
	
	public function adopt(){
		$auth = $this->di->get('auth');
		$args = $this->di->get('request')->get();
		
		if(!$auth->can('edit_product')){
			return $this->jsonResponse(array(
				'status'=>-99,
				'msg'=>'Permission Denied'
			));
		}
		
		if( isset($args['id']) && $args['id'] > 0){
			//get the product make sure we own it
			$product = Product::fromId($args['id']);
			//fetch all subsystems quickly
			$product->out();
			$product->company_id = $auth->user->company_id;
			$product->source_id = $product->id;
			$product->id = '';
			
			$product->save();
			$result = array('status'=>1,'result'=>$product->out());
			return $this->jsonResponse($result);
		}else{
			return $this->jsonResponse(array(
				'status'=>-1,
				'msg'=>'Invalid Product id'
			));	
		}
	}
	
	public function cloneProduct(){
		$auth = $this->di->get('auth');
		$args = $this->di->get('request')->get();
		
		if(!$auth->can('edit_product')){
			return $this->jsonResponse(array(
				'status'=>-99,
				'msg'=>'Permission Denied'
			));
		}
		
		if( isset($args['id']) && $args['id'] > 0){
			//get the product make sure we own it
			$product = Product::fromId($args['id']);
			
			//fetch all subsystems quickly
			$product->out();
			
			//echo "Before sanatize:".print_r($product->out(),1)."\n";
			//clear fee and variance id's
			$product->sanaticeForClone();
			
			if( !$product->is_vendor() ){
				return $this->jsonResponse(array(
					'status'=>-9,
					'msg'=>'You do not own the activity'
				));	
			}
			$product->supplier_id = $auth->user->company_id;
			$product->source_id = $product->product_id = $product->id = '';
			$product->save();
			$result = array('status'=>1,'result'=>$product->out());
			return $this->jsonResponse($result);
		}else{
			return $this->jsonResponse(array(
				'status'=>-1,
				'msg'=>'Invalid Product id'
			));	
		}
		
	}
	
	public function translate(){
		$auth = $this->di->get('auth');
		$args = $this->di->get('request')->get();
		
		if(!$auth->can('edit_product')){
			return $this->jsonResponse(array(
				'status'=>-99,
				'msg'=>'Permission Denied'
			));
		}
		
		if( !isset($args['language']) || !$args['language'] ){//TODO compare against known languages list
			return $this->jsonResponse(array(
				'status'=>-2,
				'msg'=>'Translation language required'
			));
		}
		
		if( isset($args['id']) && $args['id'] > 0){
			//get the product make sure we own it
			$product = Product::fromId($args['id']);
			//fetch all subsystems quickly
			$f = $product->out();
			//die("Translate:".print_r($f,1));
			
			$product->language = $args['language'];
			$product->workflow = 'unedited';
			$product->company_id = $auth->user->company_id;
			$product->source_id = $product->id;
			$product->id = '';
			
			$product->save();
			$result = array('status'=>1,'result'=>$product->out());
			return $this->jsonResponse($result);
		}else{
			return $this->jsonResponse(array(
				'status'=>-1,
				'msg'=>'Invalid Product id'
			));	
		}
	}

}