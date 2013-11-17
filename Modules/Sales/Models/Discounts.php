<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Discounts extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'ticket_id',
		'sale_id',
		'user_id',
		'company_id',
		'pos_location_id',
		'discount_id',
		'discount_code',
		'percent',
		'amount',
		'status',
		'created',
		'modified'
	);
}