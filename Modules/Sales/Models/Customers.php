<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Customers extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'first_name',
		'last_name',
		'address',
		'city',
		'state',
		'postal',
		'country',
		'email',
		'phone',
		'age',
		'password'
	);
}
