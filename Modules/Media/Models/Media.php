<?php
namespace Media\Models;
/*
 * The Media used by our system
 */
 
use \Molotov\Core\Models\BaseModel;
use \Media\Models\Folders;
use \Media\Models\Meta;
use \Media\Models\Repo;
class Media extends BaseModel{
	
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
    public $folder_id;

    /**
     * @Column(type="string", length=10, nullable=false)
     */
    public $type;

    /**
     * @Column(type="string", length=10, nullable=false)
     */
    public $extension;
            
    /**
     * @Column(type="float", nullable=false)
     */
    public $rating;

    /**
     * @Column(type="integer", nullable=false)
     */
    public $size;

    /**
     * @Column(type="string", length=19, nullable=false)
     */
    public $created;
    	
	protected $fields = array(
		'id',
		'name',
		'path',
		'folder_id',
		'type',
		'extension',
		'rating',
		'size',
		'created'
	);
	
	
    public function initialize()
    {
        $this->hasMany("id", "Meta", "media_id");
		$this->belongsTo("folder_id", "Folders", "id");
    }
}
