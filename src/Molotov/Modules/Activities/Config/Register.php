<?php

/* Activities module autoloader defs */
$config['namespaces']['Arez\Modules\Activities\Models'] = MODULES_DIR."/Activities/Models";
$config['namespaces']['Arez\Modules\Activities\Controllers'] = MODULES_DIR."/Activities/Controllers";
$config['namespaces']['Arez\Modules\Activities\Tests\Controllers'] = MODULES_DIR."/Activities/Test/Controllers";


/*  Activities Module Routes */
$app->map('/api/Activities/info/{id}',function($id) use ($app){
	// TODO check permission
	
	// TODO log request
	
	$Activity = new \Arez\Modules\Activities\Models\Activity($id);
	echo json_encode($Activity);
	
})->via(array('GET','POST'));

$app->post('/api/Activities/save',function() use($app){

	//fetch the Activity object as a json post
	$activityIn = $app->request->getJsonRawBody();
	
	if($activityIn->id){
		$ActivityModel = new \Arez\Modules\Activities\Models\Activities($activityIn->id);
	}else{
		$ActivityModel = new \Arez\Modules\Activities\Models\Activities();
	}
	
	$validationErrors = $ActivityModel->unserialize($activityIn);
	die("Activity Save:".json_encode($ActivityModel,1));
	
});


