<?php
namespace Molotov\Modules\Media\Models;
/*
 * The Folder models
 */
 
use Molotov\Core\Models\BaseModel;
use Molotov\Modules\Media\Models\Media;
use Molotov\Modules\Media\Models\Meta;
use Molotov\Modules\Media\Models\Repo;

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
