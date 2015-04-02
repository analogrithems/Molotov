<?php

define("MEDIA_MODULE_DIR",MODULES_DIR.'/Media');

$config['media']['ffprobe'] = '/usr/bin/ffprobe';
$config['media']['ffmpeg'] = '/usr/bin/ffmpeg';
$config['media']['zip'] = '/usr/bin/zip';

//Transcoding Strings Mine + Subsonic's
//(%s = The file to be transcoded, %b = Max bitrate of the player, %t = Title, %a = Artist, %l = Album)
$config['transcode']['flv'] 	= $config['media']['ffmpeg'].' -ss %o -i %s -async 1 -b %bk -s %wx%h -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -threads 0 -';
$config['transcode']['mp3'] 	= $config['media']['ffmpeg'].' -i %s -ab %bk -v 0 -f mp3 -';
$config['transcode']['ogg'] 	= $config['media']['ffmpeg'].' -v 0 -i %s -f ogg -vn -acodec libvorbis -ar 44100 -aq 6 -ac 2 -map_meta_data 0:0 -';
$config['transcode']['hls'] 	= $config['media']['ffmpeg'].' -ss %o -t %d -i %s -async 1 -b %bk -s %wx%h -ar 44100 -ac 2 -v 0 -f mpegts -vcodec libx264 -preset superfast -acodec libmp3lame -threads 0 -';
$config['transcode']['webm']	= $config['media']['ffmpeg'].' -ss %o -t %d -i %s -async 1 -vf lutyuv=y=val*1.3 -b %bk -s %wx%h -ar 44100 -ac 2 -v 0 -f webm -vcodec libvpx -preset superfast -acodec libvorbis -threads 0 -';


$config['media']['cachedir'] = MEDIA_MODULE_DIR . '/Cache';


//Include any vendor libs we need :)
require_once(MEDIA_MODULE_DIR . '/vendor/autoload.php');

/*  Media Module Routes */
$app->map('/api/Media/Search',function() use ($app){
	$args = array();
	 
	if($app->request->getPost('limit')) 	$args['limit'] 		= $app->request->getPost('limit');
	if($app->request->getPost('offset')) 	$args['offset'] 	= $app->request->getPost('offset');
	if($app->request->getPost('sortfield'))	$args['sortfield'] 	= $app->request->getPost('sortfield');
	if($app->request->getPost('sortdir')) 	$args['sortdir'] 	= $app->request->getPost('sortdir');
	if($app->request->getPost('artist')) 	$args['artist'] 	= $app->request->getPost('artist');
	if($app->request->getPost('album')) 	$args['album'] 		= $app->request->getPost('album');
	if($app->request->getPost('genre')) 	$args['genre'] 		= $app->request->getPost('genre');
	if($app->request->getPost('type')) 		$args['type'] 		= $app->request->getPost('type');
		
	$media = new Molotov\Modules\Media\Controllers\Search();
	return $media->action_index($args);
		
})->via(array('GET','POST'));


$app->get('/api/Media/Zip/{id}',function($id) use ($app){

	
	$media = new Molotov\Modules\Media\Controllers\MediaController();
	$media->action_download($id);
	
});

$app->get('/api/Media/Stream/{id}/{format}',function($id,$format) use ($app){

	
	$media = new Molotov\Modules\Media\Controllers\MediaController();
	$media->action_stream( $id, $format );
	
});

$app->map('/api/Media/Repo/{id}/Folders',function($id) use ($app){

	$media = new Molotov\Modules\Media\Controllers\MediaController();
	return $media->action_rootFolderInfo($id);
	
})->via(array('GET','POST'));

$app->map('/api/Media/Folder/{id}',function($id) use ($app){

	$media = new Molotov\Modules\Media\Controllers\MediaController();
	return $media->action_getFolderInfo($id);
	
})->via(array('GET','POST'));



$app->map('/api/Media/Folder/Art/{wxh}/{id}',function( $wxh, $id ) use ($app){

	$media = new Molotov\Modules\Media\Controllers\MediaController();
	return $media->action_getFolderArt($id, $wxh);
	
})->via(array('GET','POST'));




$app->map('/api/Media/Get/{id}',function($id) use ($app){

	$media = new Molotov\Modules\Media\Controllers\MediaController();
	return $media->action_getMediaInfo($id);
	
})->via(array('GET','POST'));


$app->map('/api/Media/Repo/add',function() use ($app){

	
	$media = new Molotov\Modules\Media\Controllers\RepoController();
	$args = array(
		'name'=>$app->request->getPost('name'),
		'path'=>$app->request->getPost('path'),
	);
	$media->action_create($args);
	
})->via(array('POST'));

$app->map('/api/Media/Repo/Delete/{id}',function($id) use ($app){

	$args = array();
	$args['id'] 		= (int)$app->request->getPost('limit');
	$media = new Molotov\Modules\Media\Controllers\RepoController();
	return $media->action_delete($args);
	
})->via(array('GET'));
 
 
$app->map('/api/Media/Repos',function() use ($app){

	$args = array();
	$args['limit'] 		= $app->request->getPost('limit');
	$args['offset'] 	= $app->request->getPost('offset');
	$args['sortfield'] 	= $app->request->getPost('sortfield');
	$args['sortdir'] 	= $app->request->getPost('sortdir');	
	
	$media = new Molotov\Modules\Media\Controllers\RepoController();
	return $media->action_index($args);
	
})->via(array('GET'));
