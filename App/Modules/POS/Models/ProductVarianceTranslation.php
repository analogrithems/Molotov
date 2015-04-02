<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;

class ProductVarianceTranslation extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'name'=>'',
		'product_id'=>0,
		'product_variance_id'=>0
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'name',
			'product_id',
			'product_variance_id'
		);

		$pv =  self::fromProductId($this->product_variance_id, $this->product_id);
		if($pv && isset($pv->id) && $pv->id > 0) {
			$this->id = $pv->id;
			$query = $db->createQuery()
					  	->update('product_variance_translation', $this->out($fields, true), array("id='{$this->id}'"));
			$db->execute($query->get());
		} else {
			$query = $db->createQuery()
						->insert('product_variance_translation', $this->out($fields, true));
			$db->execute($query->get());
			$this->id = $db->lastInsertId();
		}
		

		return $this;
	}
	
	public static function fromProductId( $product_variance_id, $product_id ){
		$data = self::search(array("product_id" => $product_id, 'product_variance_id'=>$product_variance_id));
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
				  ->select('DISTINCT vt.*')
				  ->from('product_variance_translation vt')
				  ->orderBy('vt.name','asc');

		if(isset($request['id'])){
			$query->where('vt.id = %d',$request['id']);
		}

		if(isset($request['name'])){
			$query->where('vt.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['product_variance_id'])){
			$query->where('vt.product_variance_id=%d', $request['product_variance_id'] );
			unset($request['product_variance_id']);
		}
		
		if(isset($request['product_id'])){
			$query->where('vt.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
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
		$db->execute( $db->createQuery()->delete('product_variance_translation', array("id={$this->id}"))->get());
	}


	
}