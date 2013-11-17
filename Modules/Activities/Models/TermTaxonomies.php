<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Taxonomy Terms
 */
 
use \Molotov\Core\Models\BaseModel; 
class TermTaxonomies extends BaseModel{

	public $term_taxonomy_id;

	public $term_id;
	
	public $taxonomy;
	
	public $description;
	
	public $parent;
	
	public $count;
	
	public $language;
	
	public $fields = array(
		'term_taxonomy_id',
		'term_id',
		'taxonomy',
		'description',
		'parent',
		'count',
		'language'
	);
	
	public function getSource()
	{
		$config   = 	$this->di->get('config');
		return $config['db']['table_prefix'] . 'term_taxonomy';
	}
	
	public function initialize(){
		$this->hasManyToMany('term_taxonomy_id','TermRelationships','term_taxonomy_id','activity_id','Activities','id');
	}
	
}