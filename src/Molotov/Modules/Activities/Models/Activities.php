<?php
namespace Arez\Modules\Activities\Models;
/*
 * The Activity model is the start of an activity
 */
 
use \Molotov\Core\Models\BaseModel;

class Activities extends BaseModel{

	public $id;
	
	public $title;
	
	public $short_title;
	
	public $description;
	
	public $short_description;
	
	public $special_instructions;
	
	public $supplier_activity_id;
	
	public $language;
	
	public $owner_id;
	
	public $supplier_id;
	
	public $seo_path;
	
	public $duration;
	
	public $parent_id;
	
	public $status;
	
	public $type;
	
	public $currency;
	
	public $rev_id;
	
	public $created;

	public $modified;
	
	public $originating_activity_id;
	
	public $min_inv;
	
	public $max_inv;
	
	public $booking_cutoff_mins;
	
	public $booking_cutoff_hours;
	
	public $cfa;
	
	public $terms_and_conditions;
	
	public $cancellation_policy;
	
	public $single_voucher;
	
	public $workflow_status;
	
	public $fields = array(
		'id',
		'title',
		'short_title',
		'description',
		'short_description',
		'special_instructions',
		'supplier_activity_id',
		'language',
		'owner_id',
		'supplier_id',
		'seo_path',
		'duration',
		'parent_id',
		'status',
		'type',
		'currency',
		'rev_id',
		'created',
		'modified',
		'originating_activity_id',
		'min_inv',
		'max_inv',
		'booking_cutoff_mins',
		'booking_cutoff_hours',
		'cfa',
		'terms_and_conditions',
		'cancellation_policy',
		'single_voucher',
		'workflow_status'
	);
	
	public $lists = array(
		'media'			=> '\Arez\Modules\Activities\Models\Media',
		'fees'			=> '\Arez\Modules\Activities\Models\Fees',
		'guest_types'	=> '\Arez\Modules\Activities\Models\GuestTypes',
		'times'			=> '\Arez\Modules\Activities\Models\Times',
		'addons'		=> '\Arez\Modules\Activities\Models\Addons',
		'catalogs'		=> '\Arez\Modules\Activities\Models\TermTaxonomies',
		'destinations'	=> '\Arez\Modules\Activities\Models\TermTaxonomies',
		'categories'	=> '\Arez\Modules\Activities\Models\TermTaxonomies',
		'moods'			=> '\Arez\Modules\Activities\Models\TermTaxonomies',
		'tags'			=> '\Arez\Modules\Activities\Models\TermTaxonomies',
	);
	
    public function initialize(){
        $this->hasMany("id", "Addons", "activity_id");
        $this->hasMany("id", "Fees", "activity_id");
        $this->hasMany("supplier_activity_id", "Times", "supplier_activity_id");
        $this->hasMany("supplier_activity_id", "Geos", "supplier_activity_id");
        $this->hasMany('id','GuestTypes','activity_id');
        $this->hasManyToMany('id','TermRelationships','activity_id','term_taxonomy_id','TermTaxonomies','term_taxonomy_id');
        $this->belongsTo("parent_id", "Activities", "id");
        
		$this->addBehavior(new \Phalcon\Mvc\Model\Behavior\Timestampable(
            array(
                'beforeCreate' => array(
                    'field' => 'created',
                    'format' => 'Y-m-d H:i:s'
                ),
                'beforeUpdate' => array(
                    'field' => 'modified',
                    'format' => 'Y-m-d H:i:s'
                ),
            )
        ));
    }	
}