<?php
namespace Auth\Models;
/*
 * User Role
 */
 
use \Molotov\Core\Models\BaseModel;

class Role extends BaseModel{
	
	protected $fields = array(
		'id',
		'name',
		'group_id',
		'capabilities'
	);
	
	public function initialize()
	{
		$this->belongsTo('group_id','\Auth\Models\Group','id',array('alias'=>'Group'));
	    
		$this->hasManyToMany(
		    "id",
		    "\Auth\Models\RoleCapabilites",
		    "role_id", "capability_id",
		    "\Auth\Models\Capability",
		    "id",
		    array('alias'=>'Capabilites')
		);
	}
	
	public function afterFetch()
	{
	       $this->capabilities = $this->getCapabilites();
	}
}