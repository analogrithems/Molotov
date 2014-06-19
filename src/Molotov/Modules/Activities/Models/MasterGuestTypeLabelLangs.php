<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Master Guest Type Label Languages
 */
 
use \Molotov\Core\Models\BaseModel; 
class MasterGuestTypeLabelLangs extends BaseModel{

	public $id;

	public $master_guest_type_label_id;
	
	public $language;
	
	public $label;
	
	public $fields = array(
		'id',
		'master_guest_type_label_id',
		'language',
		'label'
	);
	
    public function initialize(){
		$this->belongsTo('master_guest_type_label_id','MasterGuestTypeLabels', 'is');
	}
	
}