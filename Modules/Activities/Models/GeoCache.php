<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class GeoCache extends BaseModel{

	public $id;

	public $address_des;

	public $formatted_address;
		
	public $lat;
	
	public $lng;
	
	public $timezone;

	public $street;
	
	public $city;
	
	public $province;
	
	public $country;
	
	public $postalcode;
	
	public $fields = array(
		'id',
		'address_des',
		'formatted_address',
		'lat',
		'lng',
		'timezone',
		'street',
		'city',
		'province',
		'country',
		'postalcode',
		'parent_id',
		'owner_id'
	);

	
}