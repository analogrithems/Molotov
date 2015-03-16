<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;

class ProductVarianceItemTranslation extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'name'=>'',
		'sku'=>'',
		'notes'=>'',
		'product_id'=>0,
		'variance_item_id'=>0
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$fields = array(
			'name',
			'sku',
			'notes',
			'product_id',
			'variance_item_id'
		);
		$pvit =  self::fromProductId($this->variance_item_id, $this->product_id);
		if( !$this->sku && isset($pvit->sku) ){
			$this->sku = $pvit->sku;
		}
		if( !$this->sku ){
			$this->sku = $this->makeSlug($this->name);
		}
		if( $pvit ) {
			$this->id = $pcit->id;
			$query = $db->createQuery()
					  	->update('product_variance_item_translation', $this->out($fields, true), array("id='{$this->id}'"));
			$db->execute($query->get());
		} else {
			$query = $db->createQuery()
						->insert('product_variance_item_translation', $this->out($fields, true));
			$db->execute($query->get());
			$this->id = $db->lastInsertId();
		}
		

		return $this;
	}
	
	public static function fromProductId( $variance_item_id, $product_id ){
		$data = self::search(array("product_id" => $product_id, 'variance_item_id'=>$variance_item_id));
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
				  ->select('DISTINCT pvit.*')
				  ->from('product_variance_item_translation pvit')
				  ->orderBy('pvit.name','asc');

		if(isset($request['id'])){
			$query->where('pvit.id = %d',$request['id']);
		}

		if(isset($request['name'])){
			$query->where('pvit.name LIKE %s', '%'.$request['name'].'%' );
			unset($request['name']);
		}
		
		if(isset($request['variance_item_id'])){
			$query->where('pvit.variance_item_id=%d', $request['variance_item_id'] );
			unset($request['variance_item_id']);
		}
		
		if(isset($request['product_id'])){
			$query->where('pvit.product_id=%d', $request['product_id'] );
			unset($request['product_id']);
		}

		if(isset($request['term'])){
			$query->live(trim($request['term']), array( 'pvit.name', 'pvit.sku', 'pvit.notes' ));
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
		$db->execute( $db->createQuery()->delete('product_variance_item_translation', array("id={$this->id}"))->get());
	}

}