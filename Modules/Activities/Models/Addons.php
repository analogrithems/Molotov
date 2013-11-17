<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addons
 */
 
use \Molotov\Core\Models\BaseModel; 
class Addons extends BaseModel{
	public $id;

	public $title;
	
	public $type;
	
	public $activity_id;
	
	public $taxable;
	
	public $fields = array(
		'id',
		'title',
		'type',
		'activity_id',
		'taxable'
	);
	
    public function initialize(){
		$this->hasMany('id','AddonOptions','activity_addon_id');
		$this->belongsTo('activity_id','Activities', 'id');
	}
	
}