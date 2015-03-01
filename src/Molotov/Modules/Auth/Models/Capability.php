<?php
namespace Molotov\ModulesAuth\Models;
/*
 * The capability model
 */
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;

/**
 * @SWG\Model(id="Capability")
 */
class Capability extends BaseModel{

	/**
	 * @SWG\Property(name="id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="capability",type="string")
	 */

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