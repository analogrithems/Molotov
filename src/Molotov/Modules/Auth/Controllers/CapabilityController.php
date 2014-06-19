<?php
namespace Molotov\Modules\Auth\Controllers;
/*
 * The Capability Controller
 */
 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Capability;
class CapabilityController extends BaseController{
	public function getCapabilities(){
		return Capability::find();
	}
}