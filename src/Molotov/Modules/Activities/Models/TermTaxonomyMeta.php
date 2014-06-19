<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Taxonomy Terms
 */
 
use \Molotov\Core\Models\BaseModel; 
class TermTaxonomyMeta extends BaseModel{

	public $id;

	public $term_taxonomy_id;

	public $meta_key;
	
	public $meta_value;
	
	public $fields = array(
		'id',
		'term_taxonomy_id',
		'meta_key',
		'meta_value'
	);
	
	public function getSource()
	{
		$config   = 	$this->di->get('config');
		return $config['db']['table_prefix'] . 'term_taxonomy_meta';
	}
	
	public function initialize(){
	        $this->belongsTo('term_taxonomy_id','TermTaxonomies','term_taxonomy_id');
	}
	
}