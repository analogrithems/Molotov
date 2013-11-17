<?php
namespace Arez\Modules\Sales\Models;
/*
 * Generic Tracking Records for various models
 */
 
use \Molotov\Core\Models\BaseModel; 
class Tracking extends BaseModel{
	public $id;
	
	protected $fields = array(
		'id',
		'tracking_1',
		'tracking_2',
		'tracking_3'
	);
}