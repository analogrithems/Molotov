<?php
namespace Auth\Models;
/*
 * The Media used by our system
 */
 
use \Molotov\Core\Models\BaseModel;

class Profile extends BaseModel{


	public $fields = array(
		'id',
		'user_id',
		'language',
		'mailinglist'
	);
	
	public function initialize()
	{
		$this->belongsTo("user_id", "\Auth\Models\User", "id");
	}
}