<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;

/**
 * @SWG\Model(id="Profile")
 */
class Profile extends BaseModel{


	public $fields = array(
		'id',
		'user_id',
		'language',
		'mailinglist'
	);
	
	public function initialize()
	{
		$this->belongsTo("user_id", "Molotov\Modules\Auth\Models\User", "id");
	}
}