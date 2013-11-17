<?php
namespace Media\Models;
/*
 * Meta data the corresponds to the media files
 */
 
use \Molotov\Core\Models\BaseModel;

class Meta extends BaseModel{
    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * @Column(type="integer", nullable=false)
     */
    public $media_id;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_key;

    /**
     * @Column(type="string", nullable=false)
     */
    public $meta_value;

	
	protected $fields = array(
		'id',
		'media_id',
		'meta_key',
		'meta_value'
	);
}
