<?php
namespace Arez\Modules\Activities\Controllers;
/*
 * The Activity Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class Activities extends BaseController{
	 
	 public function saveActivity( $data ){
		 $activity = new \Arez\Modules\Activities\Models\Activities();
		 $activity->unserialize( $data );
		 
		 //TODO  validate structure
		 
		 //TODO set owner_id
		 
		 $activity->modified = date('Y-m-d H:i:s');
		 
		 if( !$activity->id ){
			 $activity->created = date('Y-m-d H:i:s');
			 $activity->save();
			 $activity->supplier_activity_id = $activity->id;
			 $activity->originating_activity_id = $activity->id;
			 $activity->save();
		 }
		 
		 /**
		  * Some things can only be updated on the master activity by the supplier
		  */
		 if( $activity->id === $activity->supplier_activity_id && $activity->owner_id == $activity->supplier_id ){
		 
		 	//handle times
			if( array_key_exists('times', $data) ) {
				if( is_array( $data['times'] ) ){
					foreach( $data['times']  as $_time){
						$time = new Times();
						$_time['supplier_activity_id'] = $activity->supplier_activity_id;
						$activity->times[] = $time->unserialize( $_time );
					}
				}
				//TODO update all adopted and translated versions via message server
			} 
		 }
		 
		 //todo handle guest types
		 
		 //todo handle taxonomy
		 
		 //todo handle fees
		 
		 
		 return array('status'=>'OK','activity'=>$activity->serialize());
	 }
}