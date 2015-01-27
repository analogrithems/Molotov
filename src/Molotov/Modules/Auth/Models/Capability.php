<?php
namespace Molotov\ModulesAuth\Models;
/*
 * The capability model
 */
 
use Molotov\Core\Models\BaseModel;

class Capability extends BaseModel{

	public $fields = array(
		'id',
		'capability'
	);
	
	
	public function initialize(){
		$this->hasManyToMany(
			"id",
			"Molotov\Modules\Auth\Models\RoleCapabilites",
			"capability_id","role_id",
			"Molotov\Modules\Auth\Models\Role",
			"id"
		);
	}
}