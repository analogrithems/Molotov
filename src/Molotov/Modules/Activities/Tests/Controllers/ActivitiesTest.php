<?php
namespace Arez\Modules\Activities\Tests\Controllers;
/*
 * The Activity Controller
 */
 
use Molotov\Core\Tests\UnitTestCase;

class ActivitiesTest extends UnitTestCase{
	
	public function newActivity(){
		$activity = array();
		$activity['short_title'] = 'Super Happy Fun time';
		$activity['title'] = "This is the bests time you can have in hawaii bra";
		$activity['description'] = "<div>
			<p>When you come to hawaii you bring lots of money bra, in this super amazing activity you follow Kat the Haole hunter on his adventures or capturing disillusioned and returning them to the airport</p>
			</div>";
			
		$activity['special_instructions'] = "Bring one camera";
		
		$activity['language'] = 'en-US';
		$activity['owner_id'] = 666;
		$activity['supplier_id'] = 666;
		$activity['seo_path'] = '/kat_hunter';
		$activity['type'] = 'activity';
		$activity['currency'] = 'USD';
		$activity['min_inv'] = 5;
		$activity['max_inv'] = 100;
		$activity['booking_cutoff_mins'] = 35;
		$activity['booking_cutoff_hours'] = 6;
		$activity['cfa'] = 1;
		$activity['workflow_status'] = 'unedited';
		
		//times
		$activity['times'] = array(
			array('startDayOfWeek'=>'Monday', 'endDayOfWeek'=>'Monday','startTime'=>'0900','endTime'=>'1000','rule'=>'available'),
			array('startDayOfWeek'=>'Tuesday', 'endDayOfWeek'=>'Tuesday','startTime'=>'0900','endTime'=>'1000','rule'=>'available'),
			array('startDayOfWeek'=>'Wednesday', 'endDayOfWeek'=>'Wednesday','startTime'=>'0900','endTime'=>'1000','rule'=>'available'),
			array('startDayOfWeek'=>'Thursday', 'endDayOfWeek'=>'Thursday','startTime'=>'0900','endTime'=>'1000','rule'=>'available'),
			array('startDayOfWeek'=>'Thursday', 'endDayOfWeek'=>'Thursday','startTime'=>'0900','endTime'=>'1000','rule'=>'blackout','startDate'=>'2013-10-01','endDate'=>'2013-10-30'),
			array('startDayOfWeek'=>'Friday', 'endDayOfWeek'=>'Friday','startTime'=>'0900','endTime'=>'1000','startDate'=>'2014-01-31','endDate'=>'2014-07-31','rule'=>'available'),
		);
		
		//fees
		$activity['fees'] = array(
			array('name'=>"Asshole Tax",'description'=>"You have to pay for being an asshole",'fee'=>'5.441'),
			array('name'=>"Sales Tax",'description'=>"Pay the pay",'percent'=>'5.441'),
		);
		
		
		
		return $activity;
	}


	public function testSaveActivity( ){
		//todo mock user
		//todo mock activity
		$data = $this->newActivity();
		$ac = new \Arez\Modules\Activities\Controllers\Activities();
		$result = $ac->saveActivity($data);
		print_r($result);
		$this->assertEquals( 'OK', $result['status'] );
		
	}
}