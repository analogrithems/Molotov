<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;
use Molotov\Modules\POS\Models\ProductVarianceTranslation;
use Molotov\Modules\POS\Models\ProductVarianceItem;
use Molotov\Modules\POS\Models\ProductVarianceItemTranslation;

class ProductVariance extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'name'=>'',
		'multi'=>0,
		'model'=>'',
		'product_id'=>0,
		'root_product_id'=>0,
		'parent_id'=>0,
		'required'=>0
	);
	
	private $_memory = array();
	
	public function hydrate($data){
		parent::hydrate($data);
		
		if(isset($data['items']) ){
			if( is_array($data['items']) ){
				$this->items = $data['items'];
			}
		}
	}
	
	public function __get($var){
		switch( $var ){
			case 'items':
				if( !array_key_exists( 'items', $this->_memory )){
					if($this->id > 0 && $this->product_id > 0 ){
						$data = ProductVarianceItem::search( array(
							'product_id'=>$this->product_id,
							'product_variance_id'=>$this->id
						) );
						if(!$data['total']) {
							$this->_memory['items'] = array();
						} else {
							$this->_memory['items'] = $data['results'];
						}
					}else{
						//local id or product id not set yet, so no point looking up fees
						return array();
					}
				}
				return $this->_memory['items'];
		}
	}
	
	public function __set($var,$val){
		switch($var){
			case 'items':
				$this->items = array();
				if( is_array($val) ){
					foreach($val as $item){
						if(is_array($item)){
							$this->items[] = new ProductVarianceItem($item);
						}	
					}
				}
				break;
			default:
				$this->$var = $val;
		}
	}
	
	public function out($_fields = null){
		$out= parent::out($_fields);
		if(is_null($_fields) || in_array('items', $_fields)){
			$out['items'] = array();
			if(isset($this->items) && !empty($this->items) && is_array($this->items)){
				foreach($this->items as $item){
					$out['items'][] = $item->out(array('id','name','sku','notes','amount','inventory','item_order'));
				} 
			}
		}
		return $out;
	}
	
	private function saveItems(){
		$db = \Phalcon\DI::getDefault()->get('db');
		//get current items
		$existing_items = ProductVarianceItem::getProductVarianceItem($this->product_id, $this->id);
		if( $this->id > 0){
			foreach($this->items as $item){
				if( is_object($item) ){
					$item->product_variance_id = $this->id;
					$item->product_id = $this->product_id;
					$item->root_product_id = $this->root_product_id;
					if( $existing_items && is_array($existing_items) ){
						foreach($existing_items as $index=>$_var){
							if($_var->id == $item->id){
								//update the variance items
								//TODO audit updating a variance item
								$item->save();
								unset($existing_items[$index]);
								continue 2;
							}
						}
					}
					//TODO audit adding a new variance
					$item->save();
				}else{
					throw new \Exception('Error saving variance; invalid variance');
				}
			}
			
			//remove any fees remaining in $existing_fees
			//TODO audit delete fee
			if( $this->root_product_id == $this->product_id ){
				if( !empty($existing_items) ){
					foreach($existing_items as $item){
						$item->delete();
					}
				}
			}
		}else{
			throw new \Exception('Error saving variance; id required');
		}
	}
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'multi',
			'model',
			'root_product_id',
			'parent_id',
			'required'
		);

		if($this->product_id == $this->root_product_id ){//only change when root
			if( self::fromId($this->id)) {
				$query = $db->createQuery()
						  	->update('product_variance', $this->out($fields, true), array("id='{$this->id}'"));
				$db->execute($query->get());
			} else {
				$query = $db->createQuery()
							->insert('product_variance', $this->out($fields, true));
				$db->execute($query->get());
				$this->id = $db->lastInsertId();
			}
		}
		
		//save translation name
		$translation = new ProductVarianceTranslation(array(
			'product_variance_id'=>$this->id,
			'product_id'=>$this->product_id,
			'name'=>$this->name
		));
		$translation->save();
		
		//save Items
		$this->saveItems();

		return $this;
	}
	
	public static function getProductVariances( $product_id, $root_product_id = null ){
		if( is_null($root_product_id) ){
			$root_product_id = $product_id;
		}
		
		$data = self::search(array(
			'product_id'=>$product_id,
			'root_product_id'=>$root_product_id
		));
		
		if(!$data['total']) {
			return null;
		} else {
			return $data['results'];
		}
	}
	
	public static function fromId($id){
		$data = self::search(array("id" => $id));
		if(!$data['total']) {
			return null;
		} else {
			return $data['results'][0];
		}
	}

	public static function search($request){
		$db = \Phalcon\DI::getDefault()->get('db');

		$query = $db->createQuery()
				  ->select('DISTINCT pv.*')
				  ->select('pvt.name')
				  ->from('product_variance pv')
				  ->join('product_variance_translation pvt',array('pv.id=pvt.product_variance_id'))
				  ->orderBy('pvt.name','asc');

		if(isset($request['id'])){
			$query->where('pv.id = %d',$request['id']);
		}

		if(isset($request['name'])){
			$query->where('pvt.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['root_product_id'])){
			$query->where('pv.root_product_id=%d', $request['root_product_id'] );
			unset($request['root_product_id']);
		}
		
		if(isset($request['product_id'])){
			$query->where('pvt.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
		}else{
			$query->where('pvt.product_id=pv.root_product_id');

		}


		$filter = static::buildFilter($request);

		if(isset($request['offset']) && isset($request['count'])) {
			$query->paginate($request['offset'], $request['count']);
		}
		if(isset($request['page']) && isset($request['count'])) {
			$query->paginate(($request['page']-1)*$request['count'], $request['count']);
		}

		if(isset($request['order'])){
			$query->orderby($request['order']['field'],$request['order']['direction']);
		}

		//echo "Product variance:".$query->get()."\n";
		$data = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);

		if((isset($request['offset'])||isset($request['page'])) && isset($request['count'])) {
			$total = $db->fetchAll($query->getTotal(),\Phalcon\Db::FETCH_ASSOC);
			$total = count($total);
		} else {
			$total = count($data);
		}
		$variances = array(
			'results'=>array()
		);
		
		//fetch items
		$variance_ids = array();
		foreach($data as $row){
			if( !in_array($row['id'],$variance_ids) ){
				$variance_ids = $row['id'];
			}
		}
		
		$product_variance_items = ProductVarianceItem::search(array(
			'product_variance_id'=>$variance_ids
		));
		
		
		foreach($data as $_variance) {
			if( !empty($_variance['model']) && class_exists($_variance['model']) ){
				$c = new $_variance['model']($_variance);				
			}else{
				$c = new self($_variance);//fallback to generic class
			}
			if( $product_variance_items && isset($product_variance_items['results']) && is_array($product_variance_items['results']) ){
				$c->items = array();
				foreach($product_variance_items['results'] as $pvi ){
					if( $pvi->product_variance_id == $c->id && $pvi->product_id == $c->product_id ){
						$c->items[] = $pvi;
					}
				}
			}
			$variances['results'][] = $c;
		}
		//die("SQL:".$query->get());
		$variances['total'] = $total;

		return $variances;
	}

	public function delete(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$db->execute( $db->createQuery()->delete('product_variance', array("id={$this->id}"))->get());
		//remove translation files if needed
		$vt = ProductVarianceTranslation::fromProductId( $this->id, $this->product_id );
		if($vt){
			$vt->delete();
		}
		//remove product variance items & their translations
		
	}


	
}