<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class GuestTypes extends BaseModel{

	public $id;

	public $supplier_guest_type_id;

	public $activity_id;
	
	public $supplier_activity_id;
	
	public $master_guest_type_label_id;
	
	public $guest_type_label_override;
	
	public $vendor_sku;
	
	public $notes;
	
	public $tracking_1;
	
	public $tracking_2;
	
	public $tracking_3;
	
	public $display_option;
	
	public $default_price;
	
	public $fields = array(
		'id',
		'supplier_guest_type_id',
		'activity_id',
		'supplier_activity_id',
		'master_guest_type_label_id',
		'guest_type_label_override',
		'vendor_sku',
		'notes',
		'tracking_1',
		'tracking_2',
		'tracking_3',
		'display_option',
		'default_price'
	);
	
    public function initialize(){
		$this->belongsTo('activity_id','Activities', 'id');
		$this->hasOne('master_guest_type_label_id', 'MasterGuestTypeLabels','id');
	}
	
}