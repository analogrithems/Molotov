<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;
use Molotov\Modules\POS\Models\ProductFeeTranslation;

class ProductFee extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'name'=>'',
		'sku'=>'',
		'notes'=>'',
		'amount'=>0,
		'percent'=>0,
		'product_id'=>0,
		'root_product_id'=>0,
		'required'=>0
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'amount',
			'percent',
			'root_product_id',
			'required'
		);

		if( $this->product_id == $this->root_product_id ){
			if( self::fromId($this->id)) {
				$query = $db->createQuery()
						  	->update('product_fee', $this->out($fields, true), array("id='{$this->id}'"));
				$db->execute($query->get());
			} else {
				$query = $db->createQuery()
							->insert('product_fee', $this->out($fields, true));
				$db->execute($query->get());
				$this->id = $db->lastInsertId();
			}
		}
		//save translations
		$translation = new ProductFeeTranslation(array_merge($this->out(array('name','sku','notes','product_id')),array('product_fee_id'=>$this->id)));
		$translation->save();
		$this->sku = $translation->sku;
		return $this;
	}

	public static function getProductFees($product_id, $root_product_id){
		if( is_null($product_id) ){
			return false;
		}
		
		$data = self::search(array(
			'product_id'=>$product_id,
			'root_product_id'=>$root_product_id
		));
		if(!$data['total']) {
			return array();
		} else {
			return $data['results'][0];
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
				  ->select('DISTINCT pf.*')
				  ->select('pft.name')
				  ->from('product_fee pf')
				  ->join('product_fee_translation pft',array( 'pft.product_fee_id = pf.id'))
				  ->orderBy('pft.name','asc');

		if(isset($request['id'])){
			$query->where('pf.id = %d',$request['id']);
		}

		if(isset($request['name'])){
			$query->where('pft.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['root_product_id'])){
			$query->where('pf.root_product_id=%d', $request['root_product_id'] );
			unset($request['root_product_id']);
		}
		
		if(isset($request['product_id'])){
			$query->where('pft.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
		}else{
			$query->where('pft.product_id=pf.root_product_id');
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
		$db->execute( $db->createQuery()->delete('product_fee', array("id={$this->id}"))->get());
	}


	
}