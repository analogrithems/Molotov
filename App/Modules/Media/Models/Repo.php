<?php
namespace Molotov\Modules\Media\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use Molotov\Core\Models\BaseModel; 
use Molotov\Modules\Media\Models\Media;
use Molotov\Modules\Media\Models\Meta;
use Molotov\Modules\Media\Models\Folders;

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
