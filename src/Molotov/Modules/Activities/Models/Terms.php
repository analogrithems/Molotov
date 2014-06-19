<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Taxonomy Terms
 */
 
use \Molotov\Core\Models\BaseModel; 
class Terms extends BaseModel{

	public $term_id;
	
	public $name;
	
	public $value;
	
	public $language;
	
	public $fields = array(
		'term_id',
		'name',
		'value',
		'language'
	);
	
	
	public function getSource()
	{
		$config   = 	$this->di->get('config');
		return $config['db']['table_prefix'] . 'terms';
	}
	
	public function initialize(){
		$this->belongsTo('activity_id','Activities', 'id');
	}
	
}