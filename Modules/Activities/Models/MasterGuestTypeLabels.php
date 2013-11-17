<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Master Guest Type Labels
 */
 
use \Molotov\Core\Models\BaseModel; 
class MasterGuestTypeLabels extends BaseModel{

	public $id;

	public $label;
	
	public $description;
	
	public $ruleFlag;
	
	public $fields = array(
		'id',
		'label',
		'description',
		'ruleFlag'
	);
	
    public function initialize(){
    	$this->setSource("master_guest_type_label");
		$this->hasMany('id','GuestTypes', 'master_guest_type_label_id');
		$this->hasMany('id','MasterGuestTypeLabelLangs', 'master_guest_type_label_id');
	}
	
}