<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Payments extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'sale_id',
		'user_id',
		'company_id',
		'pos_location_id',
		'customer_id',
		'payment_status',
		'payment_type_id',
		'payment_type',
		'payment_tracking_id',
		'amount',
		'currency',
		'authorization_id',
		'authorization_code',
		'type',
		'refundable'
		'reference_payment',
		'card_type',
		'last_four',
		'created',
		'modified'
	);
}