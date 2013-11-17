<?php
namespace Auth\Controllers;
/*
 * The Capability Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class Capability extends BaseController{
	public function getCapabilities(){
		return \Auth\Models\Capability::find();
	}
}