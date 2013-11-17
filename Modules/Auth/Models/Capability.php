<?php
namespace Auth\Models;
/*
 * The capability model
 */
 
use \Molotov\Core\Models\BaseModel;

class Capability extends BaseModel{

	public $fields = array(
		'id',
		'capability'
	);
	
	
	public function initialize(){
		$this->hasManyToMany(
			"id",
			"\Auth\Models\RoleCapabilites",
			"capability_id","role_id",
			"\Auth\Models\Role",
			"id"
		);
	}
}