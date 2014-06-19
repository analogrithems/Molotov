<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class GroundTransports extends BaseModel{

	public $id;

	public $activity_id;
	
	public $transport_zone;
	
	public $direction;
	
	public $vehicle_type;
	
	public $bags;
	
	public $passengers;
	
	public $fields = array(
		'id',
		'activity_id',
		'transport_zone',
		'direction',
		'vehicle_type',
		'bags',
		'passengers'
	);
	
    public function initialize(){
		$this->belongsTo('activity_id','Activities', 'id');
	}
	
}