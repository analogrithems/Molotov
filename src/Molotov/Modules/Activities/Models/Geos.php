<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class Geos extends BaseModel{

	public $id;

	public $supplier_activity_id;
	
	public $geo_info_id;
	
	public $pickupTime;
	
	public $pickupDayOfWeek;

	public $geo_type;
	
	public $fee;
	
	public $fields = array(
		'id',
		'supplier_activity_id',
		'geo_info_id',
		'pickupTime',
		'pickupDayOfWeek',
		'geo_type',
		'fee'
	);
	
    public function initialize(){
		$this->belongsTo('supplier_activity_id','Activities', 'supplier_activity_id');
		$this->hasOne('geo_info_id','GeoInfos','id');
	}
	
}