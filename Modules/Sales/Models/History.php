<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class SmartLogs extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'sale_id',
		'type_id',
		'type',
		'user_id',
		'company_id',
		'event',
		'source',
		'created',
		'modified',

		
	);
}