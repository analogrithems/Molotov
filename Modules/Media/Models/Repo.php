<?php
namespace Media\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\BaseModel; 
use \Media\Models\Media;
use \Media\Models\Meta;
use \Media\Models\Folders;

class Repo extends BaseModel{
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
    public $enabled;
	
	protected $fields = array(
		'id',
		'name',
		'path',
		'enabled'
	);
}
