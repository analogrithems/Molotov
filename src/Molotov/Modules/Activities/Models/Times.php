<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity Times
 */
 
use \Molotov\Core\Models\BaseModel; 
class Times extends BaseModel{

	public $id;
	
	public $supplier_activity_id;
	
	public $startDayOfWeek;
	
	public $endDayOfWeek;
	
	public $startTime;
	
	public $endTime;
	
	public $startDate;
	
	public $endDate;
	
	public $rule;
	
	public $fields = array(
		'id',
		'supplier_activity_id',
		'startDayOfWeek',
		'endDayOfWeek',
		'startTime',
		'endTime',
		'startDate',
		'endDate',
		'rule'
	);
	
	public function initialize(){
		$this->belongsTo('supplier_activity_id','Activities', 'supplier_activity_id');
	}
	
}