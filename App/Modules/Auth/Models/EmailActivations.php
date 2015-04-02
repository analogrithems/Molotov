<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;


/**
 * @SWG\Model(id="EmailActivations")
 */
class EmailActivations extends BaseModel{

	/**
	 * @SWG\Property(name="id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="user_id",type="integer",format="int64")
	 */
	 
	/**
	 * @SWG\Property(name="activation_key",type="string")
	 */

	/**
	 * @SWG\Property(name="type",type="string",enum="['verify','passwordreset','signup']")
	 */
	 
	/**
	 * @SWG\Property(name="created",type="string",description="standard SQL timestamp in YYYY-MM-DD HH:MM:SS format")
	 */
	 
    /**
     * @SWG\Property(
     *   name="used", type="integer", format="int32",
     *   description="Activation Key Status",
     *   enum="{'0':'unused','1':'used'}"
     * )
     */

	public $fields = array(
		'id',
		'user_id',
		'activation_key',
		'type',
		'created',
		'used'
	);
	
	public function getSource()
	{
		return 'emailactivations';
	}
	
	public function initialize()
	{
	        $this->belongsTo("user_id", "Molotov\Modules\Auth\Models\User", "id");
	}

	public function beforeValidationOnCreate()
	{
		$this->activation_key = uniqid(sha1(rand()),true);
		$this->created = date('Y-m-d H:i:s');
		$this->used = 0;
	}
	
	public function validation()
	{		
		$this->validate(new \Phalcon\Mvc\Model\Validator\Numericality(array(
			'field' => 'user_id'
		)));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\PresenceOf(array(
			'field' => 'activation_key'
		)));
		
		$this->validate(new \Phalcon\Mvc\Model\Validator\InclusionIn(array(
			'field' => 'type',
			'domain' => array('verify', 'passwordreset', 'signup')
		)));
		
		$this->validate(
			new \Phalcon\Mvc\Model\Validator\Uniqueness(
				array(
					"field"   => "activation_key",
					"message" => "This activation_key is already in use, try again"
				)
			)
		);
		
		if ($this->validationHasFailed() == true) {
			return false;
		}
	}
}