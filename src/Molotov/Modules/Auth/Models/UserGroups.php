<?php
namespace Molotov\Modules\Auth\Models;
/*
 * User Role
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;


/**
 * @SWG\Model(id="UserGroups")
 */
class UserGroups extends BaseModel{
	
	protected $fields = array(
		'id',
		'user_id',
		'group_id',
		'role_id'
	);
	
	public function getSource()
	{
		return 'usergroups';
	}

	
	public function initialize()
	{
		$this->belongsTo("user_id", "Molotov\Modules\Auth\Models\User", "id",array('alias'=>'User'));
		$this->belongsTo("group_id", "Molotov\Modules\Auth\Models\Group", "id",array('alias'=>'Group'));
		$this->belongsTo("role_id", "Molotov\Modules\Auth\Models\Role", "id",array('alias'=>'Role'));
	}
}