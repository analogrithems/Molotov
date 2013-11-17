<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Taxonomy Terms
 */
 
use \Molotov\Core\Models\BaseModel; 
class TermRelationShips extends BaseModel{

	public $activity_id;
	
	public $term_taxonomy_id;
	
	public $weight;
		
	public $fields = array(
		'activity_id',
		'term_taxonomy_id',
		'weight'
	);
	
	
	public function getSource()
	{
		$config   = 	$this->di->get('config');
		return $config['db']['table_prefix'] . 'term_relationships';
	}
	
	public function initialize(){
		$this->belongsTo('activity_id','Activities', 'id');
		$this->belongsTo('term_taxonomy_id','TermTaxonomies', 'term_taxonomy_id');
	}
	
}