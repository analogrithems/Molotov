<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class GuestTypeRestrictions extends BaseModel{

	public $id;

	public $activity_guest_type_id;
	
	public $range_type;
	
	public $min;
	
	public $max;
	
	public $unit;
	
	public $fields = array(
		'id',
		'activity_guest_type_id',
		'range_type',
		'min',
		'max',
		'unit'
	);
	
    public function initialize(){
		$this->belongsTo('activity_guest_type_id','GuestTypes', 'id');
	}
	
}