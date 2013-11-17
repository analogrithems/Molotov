<?php
namespace Auth\Models;
/*
 * User Role
 */
 
use \Molotov\Core\Models\BaseModel;

class UserGroups extends BaseModel{
	
	protected $fields = array(
		'id',
		'user_id',
		'group_id',
		'role_id'
	);
	
	public function getSource()
	{
		$config   = 	$this->_dependencyInjector->get('config');
		return $config['db']['table_prefix'] . 'usergroups';
	}

	
	public function initialize()
	{
		$this->belongsTo("user_id", "\Auth\Models\User", "id",array('alias'=>'User'));
		$this->belongsTo("group_id", "\Auth\Models\Group", "id",array('alias'=>'Group'));
		$this->belongsTo("role_id", "\Auth\Models\Role", "id",array('alias'=>'Role'));
	}
}