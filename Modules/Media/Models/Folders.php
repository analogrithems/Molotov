<?php
namespace Media\Models;
/*
 * The Folder models
 */
 
use \Molotov\Core\Models\BaseModel;
use \Media\Models\Media;
use \Media\Models\Meta;
use \Media\Models\Repo;

class Folders extends BaseModel{
    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * @Column(type="string", nullable=false)
     */
    public $path;

    /**
     * @Column(type="integer", nullable=false)
     */
    public $parent;
    
	/**
     * @Column(type="integer", nullable=false)
     */
    public $repo;

    /**
     * @Column(type="string", length=19, nullable=false)
     */
    public $created;
    
    /**
     * @Column(type="string", length=19, nullable=false)
     */
    public $updated;
	
	protected $fields = array(
		'id',
		'name',
		'path',
		'parent',
		'created',
		'updated',
		'repo'
	);
	
    public function initialize()
    {
        $this->hasMany("id", "Media", "folder_id");
    }
}
