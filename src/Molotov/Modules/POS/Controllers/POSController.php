<?php
namespace Molotov\Modules\POS\Controllers;

use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Auth\Models\User;
use Molotov\Modules\Auth\Models\Group;
use Molotov\Modules\Auth\Models\UserGroups;

class POSController extends BaseController{

	/*
	 * Wrappers arround several product controller calls
	 */		
	public function product_search(){
		$product = new ProductController();
		return $product->search();
	}

	public function product_save(){
		$product = new ProductController();
		return $product->save();		
	}
	
	public function product_delete(){
		$product = new ProductController();
		return $product->delete();
	}
	
	public function product_view($id){
		$product = new ProductController();
		return $product->view($id);
	}
	
	public function product_cloneProduct(){
		$product = new ProductController();
		return $product->cloneProduct();
	}
	
	public function product_adopt(){
		$product = new ProductController();
		return $product->adopt();
	}
}