<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;

class ProductTaxonomy extends BaseModel {

	private static $fields = array(
		'id'=>'',
		'taxonomy'=>'',
		'term'=>'',
		'slug'=>'',
		'language'=>'en_US',
		'parent_id'=>0,
		'company_id'=>''
	);
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$auth = \Phalcon\DI::getDefault()->get('auth');
		$fields = array(
			'taxonomy',
			'term',
			'slug',
			'language',
			'parent_id',
			'company_id'
		);
		
		if( !$this->term ){
			throw new Exception('Error saving taxonomy; missing term');
		}
		
		if( !$this->taxonomy ){
			throw new Exception('Error saving taxonomy; missing taxonomy');
		}
		
		//make sure term/taxonomy pair doesn't already exists
		if( !$this->id || $this->id < 1){
			$check = self::search(array(
				'term'=>$this->term,
				'taxonomy'=>$this->taxonomy,
				'language'=>$this->language,
				'company_id'=>array($auth->user->company_id,0)
			));
			
			if( isset($check['total']) && $check['total'] > 0 ){
				//get first element
				if(isset($check['results']) && isset($check['results'][0]) ){
					$this->id = $check['results'][0]->id;
				}
			}
		}
		
		if( !$this->slug ){
			$this->slug = self::makeSlug($this->term);
		}
		if( !$this->company_id ){
			$this->company_id = $auth->user->company_id;
		}
		
		if( self::fromId($this->id)) {
			$query = $db->createQuery()
					  	->update('product_taxonomy', $this->out($fields, true), array("id='{$this->id}'"));
			$db->execute($query->get());
		} else {
			$query = $db->createQuery()
						->insert('product_taxonomy', $this->out($fields, true));
			$db->execute($query->get());
			$this->id = $db->lastInsertId();
		}

		return $this;
	}
	
	public static function getProductTaxonomy( $id = nulll ){
		if( is_null($id) ){
			return false;
		}
		$db = \Phalcon\DI::getDefault()->get('db');
		$query = $db->createQuery()
					->select('ptr.product_taxonomy_id')
					->from('product_taxonomy_relationships ptr')
					->where('ptr.product_id=%d',$id);
		$r = array();
		$taxonomy_ids = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);
		if($taxonomy_ids && is_array($taxonomy_ids)){
			foreach($taxonomy_ids as $row){
				$ptr_ids[] = $row['product_taxonomy_id'];
			}
			$_results = self::search(array(
				'id'=>$ptr_ids
			));
			if( isset($_results['total']) && $_results['total'] > 0 && is_array($_results['results']) ){
				$r = $_results['results'];
			}
		}
		return $r;
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
				  ->select('DISTINCT pt.*')
				  ->from('product_taxonomy pt');

		if(isset($request['id'])){
			if( is_array($request['id']) ){
				foreach($request['id'] as $k=>$v){
					$request['id'][$k] = (int)$v;
				}
				if( !empty($request['id']) ){
					$ids = implode(', ', $request['id']);
					$query->where("pt.id IN ({$ids})");
					unset($ids);
				}				
			}else{
				$query->where('pt.id = %d',$request['id']);
			}
		}
		
		if(isset($request['query'])){
			$query->live(trim($request['query']), array('id', 'term', 'slug'));
		}

		if(isset($request['term'])){
			$query->where('pt.term = %s', $request['term']);
			unset($request['term']);
		}

		if(isset($request['taxonomy'])){
			$query->where('pt.taxonomy = %s', $request['taxonomy']);
			unset($request['taxonomy']);
		}
		
		if(isset($request['language'])){
			$query->where('pt.language = %s', $request['language']);
			unset($request['language']);
		}
		
		if(isset($request['company_id'])){
			if( !is_array($request['company_id']) ){
				$request['company_id'] = array($request['company_id']);
			}
			if( !empty($request['company_id']) ){
				$ids = implode(', ', $request['company_id']);
				$query->where("pt.company_id IN ({$ids})");
				unset($ids);
			}	
			unset($request['company_id']);
		}
		
		$filter = static::buildFilter($request);

		if(isset($reque['offset']) && isset($request['count'])) {
			$query->paginate($request['offset'], $request['count']);
		}
		if(isset($request['page']) && isset($request['count'])) {
			$query->paginate(($request['page']-1)*$request['count'], $request['count']);
		}

		if(isset($request['order'])){
			$query->orderby($request['order']['field'],$request['order']['direction']);
		}

		//echo "SQL: ".$query->get()."\n";

		$data = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);
		$r = array('total'=>0,'results'=>array());
		if((isset($request['offset'])||isset($request['page'])) && isset($request['count'])) {
			$total = $db->fetchAll($query->getTotal(),\Phalcon\Db::FETCH_ASSOC);
			$r['total'] = count($total);
		} else {
			$r['total'] = count($data);
		}
		
		foreach($data as $row) {
			$c = new self($row);
			$r['results'][] = $c;
		}

		return $r;
	}
	
	
	public function delete(){
		$db = \Phalcon\DI::getDefault()->get('db');
		
		$db->execute( $db->createQuery()->delete('product_taxonomy', array("id={$this->id}"))->get());		
	}
}