<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Financials extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'sale_id',
		'type_id',
		'type',
		'user_id',
		'company_id',
		'affiliate_id',
		'affiliate_user_id',
		'source',
		'pos_location_id',
		'transaction_amount',
		'functional_amount',
		'activity_amount',
		'transaction_rate',
		'functional_rate',
		'activity_rate',
		'transaction_currency',
		'functional_currency',
		'activity_currency',
		'created',
		'modified',
		'completed',
		'due',
		
	);
}