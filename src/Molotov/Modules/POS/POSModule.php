<?php
namespace Molotov\Modules\POS;

use Molotov\Core\Abstracts\Module;
 
class POSModule extends Module
{
	protected $routes = array(
		'/product/search' => 'product_search',
		'/product/save' => 'product_save',
		'/product/delete' => 'product_delete',
		'/product/view/{id}' => 'product_view',
		'/product/clone' => 'product_cloneProduct',
		'/product/adopt' => 'product_adopt'
	);
	
	protected $controller = 'Molotov\Modules\POS\Controllers\POSController';
	
}