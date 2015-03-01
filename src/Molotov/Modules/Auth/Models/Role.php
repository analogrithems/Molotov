<?php
namespace Molotov\Modules\Auth\Models;
/*
 * User Role
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;

/**
 * @SWG\Model(id="Role")
 */
class Role extends BaseModel{
	
	/**
	 * @SWG\Property(name="id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="name",type="string")
	 */

	/**
	 * @SWG\Property(name="group_id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="capabilities",type="array",@SWG\Items("Capability"))
	 */
	 
	protected $fields = array(
		'id',
		'name',
		'group_id',
		'capabilities'
	);
	
	public function initialize()
	{
		$this->belongsTo('group_id','Molotov\Modules\Auth\Models\Group','id',array('alias'=>'Group'));
	    
		$this->hasManyToMany(
		    "id",
		    "Molotov\Modules\Auth\Models\RoleCapabilites",
		    "role_id", "capability_id",
		    "Molotov\Modules\Auth\Models\Capability",
		    "id",
		    array('alias'=>'Capabilites')
		);
	}
	
	public function afterFetch()
	{
	       $this->capabilities = $this->getCapabilites();
	}
}