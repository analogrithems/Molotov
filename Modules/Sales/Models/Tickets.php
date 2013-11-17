<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Tickets extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'sale_id',
		'user_id',
		'company_id',
		'affiliate_id',
		'affiliate_user_id',
		'source',
		'pos_location_id',
		'pos_tracking_id',
		'company_tracking_id',
		'affiliate_tracking_id',
		'vendor_tracking_id',
		'activity_id',
		'activity_time',
		'guest_type_id',
		'guest_type_sku',
		'guest_type_label',
		'seat_id',
		'financial_id',
		'customer_id',
		'lead_guest_id',
		'pricing_id',
		'reference_ticket_id',
		'status',
		'created',
		'modified'
	);
}