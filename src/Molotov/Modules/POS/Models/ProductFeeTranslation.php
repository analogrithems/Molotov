<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;

class ProductFeeTranslation extends vaseModel {

	private static $fields = array(
		'name'=>'',
		'sku'=>'',
		'notes'=>'',
		'product_id'=>'',
		'product_fee_id'=>''
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'name',
			'sku',
			'notes',
			'product_id',
			'product_fee_id'
		);

		$pft = self::fromProductFeeId($this->product_id,$this->product_fee_id);
		
		if( $pft && $pft->id > 0){
			$this->id = $pft->id;
			if( !$this->sku && $pft->sku ){
				$this->sku = $pft->sku;
			}
		}

		if( !$this->sku ){
			$this->sku = $this->makeSlug($this->name);
		}

		if( $pft ) {
			$query = $db->createQuery()
					  	->update('product_fee_translation', $this->out($fields, true), array("product_fee_id='{$this->product_fee_id}'"));
			$db->execute($query->get());
		} else {
			$query = $db->createQuery()
						->insert('product_fee_translation', $this->out($fields, true));
			$db->execute($query->get());
			$this->id = $db->lastInsertId();
		}

		return $this;
	}
	
	public static function fromProductFeeId($product_id,$product_fee_id){
		$data = self::search(array("product_id"=>$product_id,"product_fee_id" => $product_fee_id));
		if(!$data['total']) {
			return null;
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
				  ->select('DISTINCT pft.*')
				  ->from('product_fee_translation pft')
				  ->orderBy('pft.name','asc');

		if(isset($request['name'])){
			$query->where('pft.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['product_id'])){
			$query->where('pft.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
		}
		
		if(isset($request['product_fee_id'])){
			$query->where('pft.product_fee_id=%d', $request['product_fee_id'] );
			unset($request['product_fee_id']);
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
		//die("SQL:".$query->get());
		
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
		
		$fees['total'] = $total;

		return $fees;
	}

	public function delete(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$db->execute( $db->createQuery()->delete('product_fee_translation', array("product_id={$this->product_id}","product_fee_id={$this->product_fee_id}"))->get());
	}


	
}