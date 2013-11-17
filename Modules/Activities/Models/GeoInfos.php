<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity addon options
 */
 
use \Molotov\Core\Models\BaseModel; 
class GeoInfos extends BaseModel{

	public $id;

	public $address_des;
	
	public $lat;
	
	public $lng;
	
	public $timezone;

	public $street;
	
	public $city;
	
	public $province;
	
	public $country;
	
	public $postalcode;
	
	public $parent_id;
	
	public $owner_id;
	
	public $fields = array(
		'id',
		'address_des',
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
	
    public function initialize(){
		$this->belongsTo('id','Geos', 'geo_info_id');
	}
	
}