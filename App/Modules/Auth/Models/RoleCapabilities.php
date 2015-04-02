<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The capability model
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;

/**
 * @SWG\Model(id="RoleCapabilities")
 */
class RoleCapabilities extends BaseModel{

	protected $fields = array(
		'id',
		'role_id',
		'capability_id'
	);
	
	public function getSource()
	{
		return 'role_capabilities';
	}
	
	public function initialize()
	{
		$this->belongsTo("role_id", "Molotov\Modules\\Auth\Models\Role", "id",array('alias'=>'Role'));
		$this->belongsTo("capability_id", "Molotov\Modules\\Auth\Models\Capability", "id",array('alias'=>'Capability'));
	}
}