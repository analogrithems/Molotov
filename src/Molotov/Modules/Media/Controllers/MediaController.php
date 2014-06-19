<?php
namespace Molotov\Modules\Media\Controllers;
/*
 * The Activity Controller
 */
 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Media\Models\Repo;
use Molotov\Modules\Media\Models\Media;
use Molotov\Modules\Media\Models\Meta;
use Molotov\Modules\Media\Models\Folders;
 
class MediaController extends BaseController{

	public function action_download( $id ){
		$folder_info = $this->getFolderInfo($id);
		$path = $folder_info['path'];
		$filename = $folder_info['name'].'.zip';
		$config = $this->di->get('config');	
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		//TODO get rid of leading path
		chdir(dirname($path));
		$path = basename($path);
		passthru("{$config['media']['zip']} -8 -q -r - '{$path}' | cat");

	}
	/**
	 *	
	 *	
	 */
	public function action_stream( $id, $format, $bitrate = null  ){
		$media = $this->getMediaInfo($id);
		$config = $this->di->get('config');	
		
		switch(strtolower($format)){
			case 'mp3':
				$bitrate = ($bitrate) ? $bitrate : '128k';
				$filename = $media['name'].'.mp3';
				header('Content-Disposition: inline; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Content-type: audio/mpeg');
				header('Content-Length: '.$media['size']);
				$config['transcode']['mp3'] = preg_replace('/%bk/', $bitrate, $config['transcode']['mp3']);
				$config['transcode']['mp3'] = preg_replace('/%s/', "'".$media['path']."'", $config['transcode']['mp3']);
				passthru($config['transcode']['mp3']);
				break;
			case 'ogg':
				$bitrate = ($bitrate) ? $bitrate : '128k';
				$filename = $media['name'].'.ogg';
				header('Content-Disposition: inline; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Content-type: audio/ogg');
				header('Content-Length: '.$media['size']);				
				$config['transcode']['ogg'] = preg_replace('/%bk/', $bitrate, $config['transcode']['ogg']);
				$config['transcode']['ogg'] = preg_replace('/%s/', "'".$media['path']."'", $config['transcode']['ogg']);
				passthru($config['transcode']['ogg']);
				break;
			case 'hls':
				$bitrate = ($bitrate) ? $bitrate : '128k';
				$filename = $media['name'].'.mp3';
				header('Content-Disposition: inline; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Content-type: audio/mpeg');
				header('Content-Length: '.$media['size']);				
				$config['transcode']['hls'] = preg_replace('/%bk/', $bitrate, $config['transcode']['hls']);
				$config['transcode']['hls'] = preg_replace('/%s/', "'".$media['path']."'", $config['transcode']['hls']);
				passthru($config['transcode']['hls']);
				break;
			case 'webm':
				$bitrate = ($bitrate) ? $bitrate : '128k';
				$filename = $media['name'].'.mp3';
				header('Content-Disposition: inline; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Content-type: audio/mpeg');
				header('Content-Length: '.$media['size']);				
				$config['transcode']['webm'] = preg_replace('/%bk/', $bitrate, $config['transcode']['webm']);
				$config['transcode']['webm'] = preg_replace('/%s/', "'".$media['path']."'", $config['transcode']['webm']);
				passthru($config['transcode']['webm']);
				break;
			case 'flv':
				$bitrate = ($bitrate) ? $bitrate : '128k';
				$filename = $media['name'].'.mp3';
				header('Content-Disposition: inline; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Content-type: audio/mpeg');
				header('Content-Length: '.$media['size']);				
				$config['transcode']['flv'] = preg_replace('/%bk/', $bitrate, $config['transcode']['flv']);
				$config['transcode']['flv'] = preg_replace('/%s/', "'".$media['path']."'", $config['transcode']['flv']);
				passthru($config['transcode']['flv']);
				break;
			default:
				throw new \Exception("Failed to match stream output");
				
		}
	}
	
	public function action_getFolderInfo( $id ){
		$folder = Folders::findFirst($id);
		$result = array( 
			'id'=>$folder->id,
			'name'=>$folder->name,
			'parent'=>$folder->parent,
			'repo'=>$folder->repo,
			'created'=>$folder->created,
			'breadcrumb'=>array(
				array(
					'id'=>$folder->id,
					'name'=>$folder->name
				)
			)
		);
		
		if( $folder->parent > 0 ){
			$step = $folder->parent;
			while($step > 0){
				$_tmp = Folders::findFirst($step);
				array_unshift($result['breadcrumb'],array('id'=>$_tmp->id,'name'=>$_tmp->name));
				$step = $_tmp->parent;
			}
			
		}
		
		$mediaResults = Media::find(
			array(
				"folder_id = :folder_id: ",
				"bind" => array(
					"folder_id"=>$id
				)
			)
		);

		foreach($mediaResults as $media){
			$m = array(
				'id'=>$media->id,
				'name'=>$media->name,
				'folder_id'=>$media->folder_id,
				'type'=>$media->type,
				'rating'=>$media->rating,
				'created'=>$media->created,
				'size'=>$media->size,
				'extension'=>$media->extension
			);
			$metas = Meta::find(array("media_id = :id:","bind"=>array("id"=>$media->id)));
			foreach($metas as $_meta){
				$m['meta'][$_meta->meta_key] = $_meta->meta_value;
			}
			$result['media'][] = $m;
		}
		
		//See if this folder has children
		$childFolders = Folders::find(
			array(
				'parent = :parent_id:',
				'bind'=>array(
					'parent_id'=>$id
				)
			)
		);
		foreach($childFolders as $child){
			$result['children'][] = $child->serialize();
		}
		
		return $result;
	}
	
	public function breadcumb( $id ){
		
	}
	
	//action_rootFolderInfo
	public function action_rootFolderInfo( $repo ){
		$filter = array( 
			"parent = 0 AND repo = :repo_id: ",
			'bind'=>array('repo_id' => $repo),
			'order'=>'name'
		);
		$folders = Folders::find( $filter );
		$result = array( 
			'total'=> Folders::count( $filter ),
			'repo'=>$repo,
			'status'=>'ok'
		);

		//TODO include breadcrum path 
		foreach($folders as $folder){
			$_folder = array(
				'id'=>$folder->id,
				'name'=>$folder->name,
				'parent'=>$folder->parent,
				'repo'=>$folder->repo,
				'created'=>$folder->created
			);
			$result['folders'][] = $_folder;
		}

		return $result;
	}
	
	public function action_getMediaInfo( $id ){
		$media = Media::findFirst($id);
		$result = $media->serialize();
		foreach($media->Meta as $_meta){
			$result['meta'][$_meta->meta_key] = $_meta->meta_value;
		}

		return $result;
	}

			
	public function getFolderInfo( $id ){
		$id = (int)$id;
		$folder = Folders::findFirst($id);
		return $folder->serialize();
	}
	
	public function getMediaInfo( $id ){
		$id = (int)$id;
		$folder = Media::findFirst($id);
		return $folder->serialize();
	}
	
	public function action_getFolderArt( $id, $wxh ){

		if(!preg_match('/(\d*)x(\d*)/i',$wxh,$matches) ){
			throw new \Exception("Invalid Dimensions Given");
		}else{
			$width  = $matches[1];
			$height = $matches[2];
		}
	
		$config = $this->di->get('config');
		$cachedir = $config['media']['cachedir'];
		if(!file_exists($config['media']['cachedir'])){
			if(!mkdir($config['media']['cachedir'],0744)){
				throw new \Exception("Cache dir not available, please check permissions & path ({$config['media']['cachedir']})");
			}
		}else{
			$cachefile = $cachedir.'/'.$wxh.'/'.$id.'.png';
			if(file_exists($cachedir.'/'.$wxh.'/'.$id.'.png')){
				header('Content-Type: image/png');
				readfile($cachefile);
			}else{
				if(!file_exists(dirname($cachefile))){
					if(!mkdir(dirname($cachefile),0744)){
						throw new \Exception("Failed to make cache size dir");
					}
				}
				
				if($this->resizeImage( $this->getFolderArt($id), $cachefile,$width, $height )){
					header('Content-Type: image/png');
					readfile($cachefile);
				}
			}
		}
		
	}

	function resizeImage($source_image, $destination_image,$width, $height)
	{
	
	    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image);

	    switch ($source_image_type) {
	        case IMAGETYPE_GIF:
	            $source_gd_image = imagecreatefromgif($source_image);
	            break;
	        case IMAGETYPE_JPEG:
	            $source_gd_image = imagecreatefromjpeg($source_image);
	            break;
	        case IMAGETYPE_PNG:
	            $source_gd_image = imagecreatefrompng($source_image);
	            break;

	    }
	    if (!$source_gd_image) {
		    throw new \Exception("No Source Image:".print_r(array($source_image, $destination_image,$width, $height,$source_image, $destination_image,$width, $height),1) );
	    }
	    $source_aspect_ratio = $source_image_width / $source_image_height;
	    $destination_aspect_ratio = $width / $height;
	    if ($source_image_width <= $width && $source_image_height <= $height) {
	        $destination_image_width = $source_image_width;
	        $destination_image_height = $source_image_height;
	    } elseif ($destination_aspect_ratio > $source_aspect_ratio) {
	        $destination_image_width = (int) ($height * $source_aspect_ratio);
	        $destination_image_height = $height;
	    } else {
	        $destination_image_width = $width;
	        $destination_image_height = (int) ($width / $source_aspect_ratio);
	    }
	    $destination_gd_image = imagecreatetruecolor($destination_image_width, $destination_image_height);
	    imagecopyresampled($destination_gd_image, $source_gd_image, 0, 0, 0, 0, $destination_image_width, $destination_image_height, $source_image_width, $source_image_height);
	    imagepng($destination_gd_image, $destination_image, 8);
	    imagedestroy($source_gd_image);
	    imagedestroy($destination_gd_image);
	    return $destination_image;
	}	
	
	/*
	 * getFolderArt follows a set of rules to get the art for a given folder
	 * first checks for cover image (png,jpg, gif)
	 * Search rule
	 * 1) mp3 id3 folder art
	 * 2) cover.[png|jpg|gif]
	 * 3) folder.[png|jpg|gif]
	 * 4) 	
	 *
	 * @param int $id
	 * @return string
	 */
	public function getFolderArt( $id ){
		$folder = Folders::findFirst($id);
		chdir($folder->path);
		$config = $this->di->get('config');
		$cachedir = $config['media']['cachedir'];

		foreach( glob('*.[mM][pP]3') as $file){
			$getId3 = new \GetId3\GetId3Core();
			$path = $folder->path .'/'.$file;
	        $audio = $getId3
	            ->setOptionMD5Data(true)
	            ->setOptionMD5DataSource(true)
	            ->setEncoding('UTF-8')
	            ->analyze($path)
	        ;
	
	        if (isset($audio['error'])) {
	            throw new \RuntimeException(sprintf('Error at reading audio properties from "%s" with GetId3: %s.', $mp3File, $audio['error']));
	        }           
			if( isset($audio['id3v2']) && isset($audio['id3v2']['APIC']) && isset($audio['id3v2']['APIC'][0]) && $audio['id3v2']['APIC'][0]['data'] ){

				$image_path = $cachedir . '/'.md5($path);
				$fh = fopen($image_path,'wb');
				fwrite($fh,$audio['id3v2']['APIC'][0]['data']);
				fclose($fh);
				return $image_path;
			}
		
		}
		
		foreach( glob('[cC][oO][vV][eE][rR].*') as $file){
			if( preg_match('/png|gif|jpg|jpeg/i',$file) > 0 ){
				return $folder->path .'/'.$file;
			}
		}

		foreach( glob('[fF][oO][lL][dD][eE][rR].*') as $file){
			if( preg_match('/png|gif|jpg|jpeg/i',$file) > 0 ){
				return $folder->path .'/'.$file;
			}
		}
		
		foreach( scandir($folder->path) as $file){
			if( preg_match('/png|gif|jpg|jpeg/i',$file) > 0 ){
				return $folder->path .'/'.$file;
			}
		}
		die();
	}
}