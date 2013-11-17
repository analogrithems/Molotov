<?php
namespace Auth\Models;
/*
 * The capability model
 */
 
use \Molotov\Core\Models\BaseModel;

class RoleCapabilites extends BaseModel{

	protected $fields = array(
		'id',
		'role_id',
		'capability_id'
	);
	
	public function getSource()
	{
		$config   = 	$this->_dependencyInjector->get('config');
		return $config['db']['table_prefix'] . 'rolecapabilites';
	}
	
	public function initialize()
	{
		$this->belongsTo("role_id", "\Auth\Models\Role", "id",array('alias'=>'Role'));
		$this->belongsTo("capability_id", "\Auth\Models\Capability", "id",array('alias'=>'Capability'));
	}
}