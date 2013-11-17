<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Fees extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'ticket_id',
		'sale_id',
		'user_id',
		'company_id',
		'pos_location_id',
		'transportation_id',
		'transportation_value',
		'financials',
		'history',
		'percent',
		'amount',
		'status',
		'created',
		'modified'
	);
}