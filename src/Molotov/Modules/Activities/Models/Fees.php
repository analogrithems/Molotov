<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class Fees extends BaseModel{

	public $id;

	public $supplier_activity_id;
	
	public $name;
	
	public $fee;
	
	public $percent;
	
	public $description;
	
	public $fields = array(
		'id',
		'supplier_activity_id',
		'name',
		'fee',
		'percent',
		'description'
	);
	
    public function initialize(){
		$this->belongsTo('activity_id','Activities', 'id');
	}
	
}