<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class TicketOptions extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'ticket_id',
		'sale_id',
		'user_id',
		'company_id',
		'pos_location_id',
		'option_id',
		'option_value',
		'amount',
		'financial_id',
		'status',
		'created',
		'modified'
	);
}