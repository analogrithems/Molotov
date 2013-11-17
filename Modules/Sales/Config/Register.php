<?php

/* Sales module autoloader defs */
$config['namespaces']['Arez\Modules\Sales\Models'] = MODULES_DIR."/Sales/Models";
$config['namespaces']['Arez\Modules\Sales\Controllers'] = MODULES_DIR."/Sales/Controllers";



/*  Sales Module Routes */
$app->map('/api/sales/info/{id}',function($id) use ($app){
	// TODO check permission
	
	// TODO log request
	
	$sale = new \Arez\Modules\Sales\Models\Sale($id);
	echo json_encode($sale);
	
})->via(array('GET','POST'));

$app->post('/api/sales/save',function() use($app){

	//fetch the sale object as a json post
	$saleIn = $app->request->getJsonRawBody();
	
	if($saleIn->id){
		$saleModel = new \Arez\Modules\Sales\Models\Sale($saleIn->id);
	}else{
		$saleModel = new \Arez\Modules\Sales\Models\Sale();
	}
	
	$validationErrors = $saleModel->unserialize($saleIn);
	die("Sale Save:".json_encode($saleModel,1));
	
});


