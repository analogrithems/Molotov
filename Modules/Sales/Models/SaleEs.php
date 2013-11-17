<?php
namespace Arez\Modules\Sales\Models;
/*
 * The Sale Model Defines the standardized sale model
 */
 
use \Molotov\Core\Models\ElasticModel;
class SaleEs extends Sale implements ElasticModel{
	protected $index = 'sales';

}