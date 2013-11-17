<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
class Sales extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'owner_id',
		'created',
		'modified',
		'lead_guest_id',
		'hotel_room',
		'hotel_id',
		'pos_location_id',
		'affiliate_id',
		'sale_status',
		'user_id',
		'balance',
		'tax',
		'total',
		'tickets',
		'ticket_options',
		'discounts',
		'payments',
		'history'
	);
	
	
	public function addTicket( $data = array() ){
	
		$_newTicket = new Ticket( );
		$_newTicket->unserialize( $data );
		if( $_newTicket->error ){
			//todo return error
		}else{
			push( $this->tickets, $_newTicket);
			
		}
		
	}
	
	public function addTicketOption( $data = array() ){
		
	}
	
	public function addPayment( $data = array() ){
		
	}
	
	public function addDiscount( $data = array() ){
		
	}
	
	public function addTransportation( $data = array() ){
		
	}
	
	public function addSmartLog( $data = array() ){
		
	}	
} 