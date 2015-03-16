<?php
namespace Molotov\Modules\Product\Models;

use Molotov\Core\Models\BaseModel;
use Molotov\Modules\Product\Models\ProductVarianceItemTranslation;

class ProductVarianceItem extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'name'=>'',
		'sku'=>'',
		'notes'=>'',
		'amount'=>0,
		'inventory'=>'',
		'item_order'=>100,
		'product_id'=>0,
		'root_product_id'=>0,
		'product_variance_id'=>0
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'amount',
			'inventory',
			'product_variance_id',
			'root_product_id',
			'item_order'
		);

		if($this->product_id == $this->root_product_id ){
			if( self::fromId($this->id)) {
				$query = $db->createQuery()
						  	->update('product_variance_item', $this->out($fields, true), array("id='{$this->id}'"));
				$db->execute($query->get());
			} else {
				$query = $db->createQuery()
							->insert('product_variance_item', $this->out($fields, true));
				$db->execute($query->get());
				$this->id = $db->lastInsertId();
			}
		}
		
		//save translation name
		$translation = new ProductVarianceItemTranslation(array(
			'variance_item_id'=>$this->id,
			'product_id'=>$this->product_id,
			'name'=>$this->name,
			'sku'=>$this->sku,
			'notes'=>$this->notes
		));
		$translation->save();
		$this->sku = $translation->sku;

		return $this;
	}
	
	public static function getProductVarianceItem( $product_id, $product_variance_id ){
		if( is_null($product_id) || is_null($product_variance_id) ){
			return false;
		}
		
		$data = self::search(array(
			'product_id'=>$product_id,
			'product_variance_id'=>$product_variance_id
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
				  ->select('DISTINCT pvi.*')
				  ->select('pvit.name, pvit.sku, pvit.notes')
				  ->from('product_variance_item pvi')
				  ->join('product_variance_item_translation pvit',array('pvi.id=pvit.variance_item_id'))
				  ->orderBy('pvi.item_order','asc')
				  ->orderBy('pvit.name','asc');

		if(isset($request['id'])){
			$query->where('pvi.id = %d',$request['id']);
		}

		if(isset($request['name'])){
			$query->where('pvit.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['root_product_id'])){
			$query->where('pvi.root_product_id=%d', $request['root_product_id'] );
			unset($request['root_product_id']);
		}
		
		
		if(isset($request['product_variance_id'])){
			if(is_array($request['product_variance_id'])){
				$pvids = implode(', ', $request['product_variance_id']);
				if(!empty($pvids)){
					$query->where("pvi.product_variance_id in ({$pvids})");
				}
			}else{
				$query->where('pvi.product_variance_id=%d', $request['product_variance_id'] );
			}
			unset($request['product_variance_id']);
		}
		
		if(isset($request['product_id'])){
			$query->where('pvit.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
		}else{
			$query->where('pvit.product_id=pvi.root_product_id');

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

		$data = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);

		if((isset($request['offset'])||isset($request['page'])) && isset($request['count'])) {
			$total = $db->fetchAll($query->getTotal(),\Phalcon\Db::FETCH_ASSOC);
			$total = count($total);
		} else {
			$total = count($data);
		}
		$fees = array(
			'results'=>array()
		);
		foreach($data as $fee) {
			$c = new self($fee);
			$fees['results'][] = $c;
		}
		//die("SQL:".$query->get());
		$fees['total'] = $total;

		return $fees;
	}

	public function delete(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$db->execute( $db->createQuery()->delete('product_variance_item', array("id={$this->id}"))->get());
		//remove translation files if needed
		$pvit = ProductVarianceItemTranslation::fromProductId( $this->id, $this->product_id );
		if($pvit){
			$pvit->delete();
		}
		//remove product variance items & their translations
		
	}


	
}