<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Core\Models\BaseModel;
use Molotov\Modules\POS\Models\ProductTaxonomy;
use Molotov\Modules\POS\Models\ProductFee;
use Molotov\Modules\POS\Models\ProductFeeTranslation;
use Molotov\Modules\POS\Models\ProductVariance;
use Molotov\Modules\POS\Models\ProductVarianceTranslation;
use Molotov\Modules\POS\Models\ProductVarianceItem;
use Molotov\Modules\POS\Models\ProductVarianceItemTranslation;
//ProductVariance

class Product extends BaseModel {

	private static $fields = array(
		'id'=>0,
		'name'=>'',
		'short_description' => '',
		'description' => '',
		'special_instructions' => '',
		'language'=>'en_US',
		'currency'=>'USD',
		'status'=>'active',
		'workflow'=>'complete',
		'company_id'=>'',
		'supplier_id'=>'',
		'product_id'=>'',
		'product_type'=>'',
		'source_id'=>'',
		'parent_id'=>0,
		'created'=>''
	);
	
	private $_memory = array();
	
	public function hydrate($data){
		parent::hydrate($data);
		
		$l = array('fees','meta','variance','taxonomy');
		foreach($l as $v){			
			if(isset($data[$v]) ){
				$this->{$v} = $data[$v];
			}
		}
	}
	
	public function out($_fields = null){
		$out = parent::out($_fields);
		if(is_null($_fields) || in_array('fees', $_fields)){
			if(is_array($this->fees)){
				foreach($this->fees as $fee){
					if(is_object($fee)) $out['fees'][] = $fee->out(array('id','name','sku','notes','amount','percent','required'));
				} 
			}
		}
		if(is_null($_fields) || in_array('variance', $_fields)){
			if(is_array($this->variance)){
				foreach($this->variance as $_variance){
					if(is_object($_variance)) $variance = $_variance->out(array('id','name','multi','required','parent_id','model','items'));
					$out['variance'][] = $variance;
				} 
			}
		}
		if(is_null($_fields) || in_array('taxonomy', $_fields)){
			if(is_array($this->taxonomy)){
				foreach($this->taxonomy as $taxonomy){
					if(is_object($taxonomy)) $out['taxonomy'][] = $taxonomy->out(array('id','taxonomy','term','slug','parent_id','language'));
				} 
			}
		}
		if(is_null($_fields) || in_array('meta', $_fields)){
			if(is_array($this->meta)){
				foreach($this->meta as $mk=>$mv){
					$out['meta'][$mk] = $mv;
				} 
			}
		}
		return $out;
	}

	public function __get($var){
		switch( $var ){
			case 'fees':
				if( !array_key_exists( 'fees', $this->_memory )){
					if($this->id > 0 && $this->product_id > 0 ){
						$data = ProductFee::search( array(
							'product_id'=>$this->od,
							'root_product_id'=>$this->product_id
						) );
						if(!$data['total']) {
							$this->_memory['fees'] = array();
						} else {
							$this->_memory['fees'] = $data['results'];
						}
					}else{
						//local id or product id not set yet, so no point looking up fees
						return array();
					}
				}
				return $this->_memory['fees'];
				
			case 'meta':
				if( !array_key_exists( 'meta', $this->_memory )){
					if($this->id > 0 ){
						$this->_memory['meta'] = $this->getMeta();
					}else{
						//local id not set yet, so no point looking up meta
						return array();
					}
				}
				return $this->_memory['meta'];
			
			case 'taxonomy':
				if( !array_key_exists( 'taxonomy', $this->_memory )){
					if($this->id > 0  ){
						$data = ProductTaxonomy::getProductTaxonomy($this->id);
						if(!$data) {
							$this->_memory['taxonomy'] = array();
						} else {
							$this->_memory['taxonomy'] = $data;
						}
					}else{
						//local id not set yet, so no point looking up taxonomy
						return array();
					}
				}
				return $this->_memory['taxonomy'];
				
			case 'variance':
				if( !array_key_exists( 'variance', $this->_memory )){
					if($this->id > 0  ){
						$data = ProductVariance::getProductVariances($this->id,$this->product_id);
						if(!$data) {
							$this->_memory['variance'] = array();
						} else {
							$this->_memory['variance'] = $data;
						}
					}else{
						//local id not set yet, so no point looking up taxonomy
						return array();
					}
				}
				return $this->_memory['variance'];
		}
	}
	
	public function __set($var,$val){
		switch($var){
			case 'fees':
				$this->fees = array();
				if( is_array($val) ){
					foreach($val as $fee){
						if(is_array($fee)){
							$this->fees[] = new ProductFee($fee);
						}	
					}
				}
				break;
			case 'taxonomy':
				$this->taxonomy = array();
				if( is_array($val) ){
					foreach($val as $tx){
						if(is_array($tx)){
							$this->taxonomy[] = new ProductTaxonomy($tx);
						}	
					}
				}
				break;
			case 'meta':
				$this->meta = array();
				if( is_array($val) ){
					foreach($val as $k=>$v){
						$this->meta[$k] = $v;
					}
				}
				break;
			case 'variance':
				$this->variance = array();
				if( is_array($val) ){
					foreach($val as $variance){
						$this->variance[] = new ProductVariance($variance);
					}
				}
				break;
			default:
				$this->$var = $val;
		}
	}
	
	
	public function beforeSave(){
		
	}
	
	public function afterSave(){
		
	}
	
	public function beforeCreate(){
		
	}
	
	public function afterCreate(){
		
	}
	
	public function beforeUpdate(){
		
	}
	
	public function afterUpdate(){
		
	}
	
	public function beforeDelete(){
		
	}
	
	public function afterDelete(){
		
	}
	
	public function is_root(){
		if($this->id == $this->product_id){
			return true;
		}
		return false;		
	}
	
	public function is_vendor(){
		$auth = \Phalcon\DI::getDefault()->get('auth');
		if($this->supplier_id == $auth->user->company_id){
			return true;
		}
		return false;
		
	}
	
	private function saveTaxonomy(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$existing_taxonomy = ProductTaxonomy::getProductTaxonomy($this->id);
		
		if( $this->id > 0){
			foreach($this->taxonomy as $taxonomy){
				if( is_object($taxonomy) && 'Molotov\Modules\POS\Models\ProductTaxonomy' == get_class($taxonomy) ){
					if( !$taxonomy->id ){
						$taxonomy->save();
					}
					
					//skip taxonomy that already exists
					if( $existing_taxonomy && is_array($existing_taxonomy) ){
						foreach($existing_taxonomy as $index=>$et){
							if($et->id == $taxonomy->id){
								unset($existing_taxonomy[$index]);
								
								continue 2;
							}
						}
					}
					//TODO audit adding a new taxonomy
					$db->execute($db->createQuery()->insert(
						'product_taxonomy_relationships', 
						array(
							'product_id'=>$this->id,
							'product_taxonomy_id'=>$taxonomy->id
						)
					)->get());
				}else{
					throw new \Exception('Error saving taxonomy; invalid taxonomy:'.print_r($taxonomy,1));
				}
			}
			
			//remove any taxonomy remaining in $existing_taxonomy
			$et_ids = array();
			foreach($existing_taxonomy as $et){
				$et_ids[] = $et->id;
			}
			if( !empty($et_ids) ){
				$ids = implode(', ', $et_ids);
				//TODO audit removing taxonomy
				$db->execute( $db->createQuery()->delete('product_taxonomy_relationships', array("product_id={$this->id}","product_taxonomy_id in ({$ids})"))->get());
				unset($ids,$et_ids);
			}
		}else{
			throw new \Exception('Error saving taxonomy; id required');
		}
	}
	
	private function saveMeta(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$existing_meta = self::getProductMeta($this->id);
		
		if( $this->id > 0){
			foreach($this->meta as $meta_key=>$meta_value){
				//skip meta that already exists
				if( $existing_meta && is_array($existing_meta) ){
					foreach($existing_meta as $index=>$em){
						if($index == $meta_key && $em == $meta_value){
							unset($existing_meta[$index]);
							continue 2;
						}elseif($index == $meta_key && $em != $meta_value){
							
							//TODO audit update meta_key
							$db->execute($db->createQuery()->update(
								'product_meta', 
								array(
									'meta_value'=>$meta_value
								),
								array(
									'meta_key'=>$meta_key,
									'product_id'=>$this->id
								)
							)->get());
							unset($existing_meta[$index]);
							continue 2;
						}
					}
				}
				
				
				//TODO audit adding a new/changed meta
				$db->execute($db->createQuery()->insert(
					'product_meta', 
					array(
						'product_id'=>$this->id,
						'meta_key'=>$meta_key,
						'meta_value'=>$meta_value
					)
				)->get());
			}
			
			//remove any meta remaining in $existing_meta
			$em_ids = array();
			foreach($existing_meta as $em){
				$em_ids[] = $em['id'];
			}
			if( !empty($et_ids) ){
				$ids = implode(', ', $em_ids);
				//TODO audit removing taxonomy
				$db->execute( $db->createQuery()->delete('product_meta', array("id in ({$ids})"))->get());
				unset($ids,$em_ids);
			}
		}else{
			throw new \Exception('Error saving taxonomy; id required');
		}
	}
	
	private function saveProductVariances(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$existing_variances = ProductVariance::getProductVariances($this->id,$this->product_id);
		
		if( $this->id > 0){
			foreach($this->variance as $variance){
				//make sure the variance is set to this product
				$variance->product_id = $this->id;
				$variance->root_product_id = $this->product_id;
				if( is_object($variance) ){
					if( $existing_variances && is_array($existing_variances) ){
						foreach($existing_variances as $index=>$_var){
							if($_var->id == $variance->id){
								//update the variance
								//TODO audit updating a variance
								$variance->save();
								unset($existing_variances[$index]);
								continue 2;
							}
						}
					}
					//TODO audit adding a new variance
					$variance->save();
				}else{
					throw new \Exception('Error saving variance; invalid variance');
				}
			}
			
			//remove any fees remaining in $existing_fees
			//TODO audit delete fee
			if( $this->id == $this->product_id ){
				if( !empty($existing_variances) ){
					foreach($existing_variances as $variance){
						$variance->delete();
					}
				}
			}
		}else{
			throw new \Exception('Error saving variance; id required');
		}
	}
	
	private function saveFees(){
		$db = \Phalcon\DI::getDefault()->get('db');
		$existing_fees = ProductFee::getProductFees($this->id,$this->product_id);
		
		if( $this->id > 0){
			foreach($this->fees as $fee){
				//make sure the fee is set to this product
				$fee->product_id = $this->id;
				$fee->root_product_id = $this->product_id;
				if( is_object($fee) && 'Molotov\Modules\POS\Models\ProductFee' == get_class($fee) ){
					if( $existing_fees && is_array($existing_fees) ){
						foreach($existing_fees as $index=>$ef){
							if($ef->id == $fee->id){
								//update the fee
								//TODO audit updating a fee
								$fee->save();
								unset($existing_fees[$index]);
								continue 2;
							}
						}
					}
					//TODO audit adding a new fee
					$fee->save();
				}else{
					throw new \Exception('Error saving fee; invalid fee');
				}
			}
			
			//remove any fees remaining in $existing_fees
			//TODO audit delete fee
			if( !empty($existing_fees) ){
				foreach($existing_fees as $fee){
					$fee->delete();
				}
			}
		}else{
			throw new \Exception('Error saving fees; id required');
		}
	}
	
	public static function getProductMeta( $id = null ){
		if( is_null($id) ){
			return false;
		}
		$db = \Phalcon\DI::getDefault()->get('db');
		$query = $db->createQuery()
					->select('pm.*')
					->from('product_meta pm')
					->where('pm.product_id=%d',$id);
		$r = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);
		$results = array();
		foreach($r as $row){
			$c = array(
				$row['meta_key']=>$row['meta_value']	
			);
			$results[] = $c;
			
		}
		return $results;
	}
	
	public function save(){
		$db = \Phalcon\DI::getDefault()->get('db');
		//Sart The Save loop/events
		$this->beforeSave();
		$fields = array(
			'name',
			'short_description',
			'description',
			'special_instructions',
			'language',
			'currency',
			'status',
			'workflow',
			'company_id',
			'supplier_id',
			'product_id',
			'product_type',
			'source_id',
			'parent_id',
			'created',
			'modified'
		);
		
		$this->modified = date('Y-m-d H:i:s');

		if( $this->id && self::fromId($this->id) ){
			$this->beforeUpdate();
			
			//TODO check if fields are allowed to be edited
			$_tmp_product = self::fromId($this->id);
			
			$query = $db->createQuery()->update('product', $this->out($fields, true), array("id='{$this->id}'"));
			$db->execute($query->get());
			
			//save taxonomy
			$this->saveTaxonomy();
			//save meta
			$this->saveMeta();
			//save fees
			$this->saveFees();
			//save variances
			$this->saveProductVariances();
			
			$this->afterUpdate();
		}else{
			$this->beforeCreate();
			
			$this->created = date('Y-m-d H:i:s');
			$this->company_id = $this->supplier_id = \Phalcon\DI::getDefault()->get('auth')->user->company_id;
			$query = $db->createQuery()
						->insert('product', $this->out($fields, true));
			$db->execute($query->get());
			$this->id = $db->lastInsertId();
			
			//if this is the root activity, set the product_id & source to self
			if( !$this->source_id && !$this->product_id){
				$this->source_id = $this->id;
				$this->product_id = $this->id;	
				//update source & product id's
				$db->execute($db->createQuery()->update('product', $this->out($fields, true), array("id='{$this->id}'"))->get());
			}
			
			//die("Taxonomy:".print_r($this->taxonomy,1));
			
			//save taxonomy
			$this->saveTaxonomy();
			//save meta
			$this->saveMeta();
			//save fees
			$this->saveFees();
			//save variances
			$this->saveProductVariances();
			
			$this->afterCreate();
		}
		
		$this->afterSave();
	}
	
	public static function getCompanyId($id){
		$db = \Phalcon\DI::getDefault()->get('db');
		$query = $db->createQuery()
					->select('p.company_id')
					->from('product p')
					->where('p.id=%d',$id);
		$result = $db->fetchOne($query->get(),\Phalcon\Db::FETCH_ASSOC);
		
		if(isset($result['company_id'])){
			return $result['company_id'];
		}
		return false;
	}


	public function getMeta( $field = null ){
		$db = \Phalcon\DI::getDefault()->get('db');
		$query = $db->createQuery()
				  ->select('pm.*')
				  ->from('product_meta pm')
				  ->where("pm.product_id=%d",$this->id)
				  ->orderby('pm.meta_key','asc');
				  
		if(!is_null($field) && !empty($field) ){
			$query->where("pm.meta_key=%s",$field);
		}
		$data = $db->fetchAll($query->get(),\Phalcon\Db::FETCH_ASSOC);

		$this->_meta = array();
		foreach($data as $row){
			$_value = json_decode($row['meta_value']);//decode if json.
			$this->_meta[$row['meta_key']] = (json_last_error() == JSON_ERROR_NONE) ? $_value : $row['meta_value'];
		}
		return $this->_meta;
	}

	public function setMeta( $field, $value ){
		$db = \Phalcon\DI::getDefault()->get('db');
		@$this->delMeta($field);

		if(is_array($value) || is_object($value)){
			$value = json_encode($value,JSON_UNESCAPED_UNICODE);
		}
		
		return $db->execute($db->createQuery()->insert('product_meta', array('product_id'=>$this->id,'meta_key'=>$field,'meta_value'=>$value))->get());
	}
	
	public function delMeta( $field ){
		$db = \Phalcon\DI::getDefault()->get('db');
		
	    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
	    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
	
	    $field =  str_replace($search, $replace, $field);
		return 	$db->execute($db->createQuery()->delete('product_meta', array("product_id={$this->id}","meta_key='{$field}'"))->get());
	}
	
	public static function searchQuery( &$request ){
		$db = \Phalcon\DI::getDefault()->get('db');
		$query = $db->createQuery()
				  ->select('distinct p.*')
				  ->from('product p')
				  ->orderby('p.name','asc');
		
		$number_fields = array(
			'id'=>'p.id',
			'company_id'=>'p.company_id',
			'supplier_id'=>'p.seller_id',
			'source_id'=>'p.source_id',
			'product_id'=>'p.product_id',
			'parent_id'=>'p.parent_id'
		);
		foreach($number_fields as $k=>$v){
			if( isset($request[$k]) ){				
				if( is_array($request[$k]) ){
					$ids = implode(', ', $request[$k]);
					$query->where($v . " IN ({$ids})");
					unset($ids);
				}else{
					$query->where($v . ' = %d',$request[$k]);
				}
			}
		}
	
		$string_fields = array(
			'language'=>'p.language',
			'currency'=>'p.currency',
			'status'=>'p.status',
			'product_type'=>'p.product_type'
		);
		foreach($string_fields as $k=>$v){
			if( isset($request[$k]) ){				
				if( is_array($request[$k]) ){
					foreach($request[$k] as $n=>$ni){
						$request[$k][$n] = "'{$ni}'";
					}
					$ids = implode(', ', $request[$k]);
					$query->where($v . " IN ({$ids})");
					unset($ids);
				}else{
					$query->where($v . ' = %s',$request[$k]);
				}
			}
		}

		if(isset($request['term'])){
			$query->live(trim($request['term']), array( 'p.id', 'p.name', 'p.description', 'p.short_description' ));
		}
		
		return $query;

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

		$query = self::searchQuery($request);

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
		$products = array(
			'results'=>array()
		);
		foreach($data as $product) {
			$c = new self($product);

			$products['results'][] = $c;
		}
		$products['total'] = $total;

		return $products;
	}
	
	public function sanaticeForClone(){
		$fees = $this->_memory['fees'];
		$variance = $this->_memory['variance'];
		foreach($fees as $k=>$v){
			if(isset($v->id) ){
				$v->id = '';
			}

			$fees[$k] = $v;
		}
		$this->_memory['fees'] = $fees;

		foreach($variance as $k=>$v){
			if(isset($v->id) ){
				$v->id = '';
			}

			if(isset($v->items) && is_array($v->items) ){
				foreach($v->items as $i=>$l){
					if(isset($l->id)){
						$l->id = '';
					}
					$v->items[$i] = $l;
				}
			}

			$variance[$k] = $v;
		}
		$this->_memory['variance'] = $variance;
		
	}
	
}