<?php
namespace Molotov\Modules\POS\Models;

use Molotov\Modules\POS\Models\Product;

class Activity extends Product {

	private static $fields = array(
		'address_id'=>'',
		'cfa'=>'1',
		'cutoff_hours'=>48,
		'cutoff_minutes'=>0,
		'book_until_end'=>0,
		'destination'=>'',
		'duration'=>''
	);
	public $fees = array();
	public $variances = array();
	
	public function save(){
		parent::save();//do everything product does, and save these variances

		if( $this->is_root() ){
			$this->saveActivityDetails();
		}
	}
	
	public function saveActivityDetails(){
		$this->saveAddress();
		
		$fields = array(
			'id',
			'product_id',
			'address_id',
			'cfa',
			'cutoff_hours',
			'cutoff_minutes',
			'book_until_end',
			'destination',
			'duration'
		);
		
		if($this->id && $this->id > 0){
			$query = $db->createQuery()->update('activity', $this->out($fields, true), array("id='{$this->id}'"));
			$db->execute($query->get());
		}else{
			$query = $db->createQuery()->insert('activity', $this->out($fields, true));
			$db->execute($query->get());
		}
		
		
	}
	
	public function out($_fields = null, $password = false){
		$out= parent::out($_fields);
		if(is_null($_fields) || in_array('address', $_fields)){
			if(isset($this->_add) && !empty($this->_add) && is_object($this->_add)){
				$out['address'] = $this->_add->out();
			}
		}
		return $out;
	}

	public function __get($var){
		if($var == 'address'){
			if($this->address_id === 0){
				return null;
			}
			if(isset($this->_add) && !empty($this->_add) && is_object($this->_add)){
				return $this->_add;
			}
			$this->_add = Address::fromId($this->address_id);
			return $this->_add;
		}
	}
	
	public function __set($var,$val){
		switch($var){
			case 'address':
				$this->_add = new Address();
				$this->_add->hydrate($val);
				break;
			default:
				$this->$var = $val;
		}
	}
	
	public function saveAddress(){
		if(isset($this->address) && !empty($this->address) && is_object($this->address)){
			$this->address->type = 'hotel';
			$this->address->save();
			$this->address_id = $this->address->id;
		}
	}
		
}