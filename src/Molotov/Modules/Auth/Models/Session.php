<?php
namespace Molotov\Modules\Auth\Models;
/*
 * The Media used by our system
 */
 
use Swagger\Annotations as SWG;
use Molotov\Core\Models\BaseModel;


/**
 * @SWG\Model(id="Session")
 */
class Session extends BaseModel{

	public $fields = array(
		'id',
		'token',
		'user_id',
		'session',
		'ip',
		'created'
	);
	
	
	public function beforeSave()
	{
		if(is_array($this->session)){
			$this->session = json_encode($this->session);
		}
		return true;
	}
	
	public function afterFetch()
	{
		if( $this->di->get('utils')->is_json($this->session) ) $this->session = json_decode($this->session,1);
	}
	
	function __destruct()
	{
		$this->save();
	}
}