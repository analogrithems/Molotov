<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class AddonOptions extends BaseModel{
	public $id;

	public $activity_addon_id;
	
	public $choice;
	
	public $fee;
	
	public $type;
	
	public $fields = array(
		'id',
		'activity_addon_id',
		'choice',
		'fee',
		'type'
	);
	
    public function initialize(){
		$this->belongsTo('activity_addon_id','Addons', 'id');
	}
	
}