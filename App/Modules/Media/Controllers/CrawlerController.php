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
 
class CrawlerController extends BaseController{
	 
	public function scanRepos(){
		set_time_limit(0);
		foreach( $this->getRepos() as $repo ){
			
			$this->crawl($repo);
			
		}
		
	}
	
	public function getRepos(){
		return Repo::find(array("enabled=1"));
	}
	
	private function getParentID( $path ){
		$parameters = array(
		    "path" => $path
		);
		$types = array(
		    "path" => \Phalcon\Db\Column::BIND_PARAM_STR
		);
		$_folder = Folders::find(
			array(
				"path = :path:",
				"bind" => $parameters
			)
		);
		
		if( 1 < count($_folder)){
			die("Error:".print_r($_folder,1));
			throw new \Exception('To many folder matches:'.print_r(json_encode($_folder),1));
			return false;
		}elseif( 1 == count($_folder) ){
			//echo "Find Folder\n";
			return $_folder[0]->id;
			
		}
		return 0;
	}
	
	public function crawl( $repo ){
		$repo_path = realpath( $repo->path );
		$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($repo_path), \RecursiveIteratorIterator::SELF_FIRST);
		$meta_fields = array('bit_rate','duration','start_time','nb_streams','mime');
		$tags = array('title','artist','album','date','genre','track');
		foreach($objects as $path => $object){
			//TODO check if we already know about file

			if( '.'  == $object->getFilename() ) continue;
			if( '..' == $object->getFilename() ) continue;
			
			//if file starts with a . skip
			if( preg_match('/^\./',$object->getFilename()) > 0 ) continue;
			
			$parameters = array(
			    "path" => $path
			);
			$types = array(
			    "path" => \Phalcon\Db\Column::BIND_PARAM_STR
			);

			if($object->isDir()){
				//echo "Looking for $path\n";
				$_folder = Folders::find(
					array(
						"path = :path:",
						"bind" => $parameters
					)
				);
				
				if( 1 < count($_folder)){
					throw new \Exception('To many folder matches');
				}elseif( 1 == count($_folder) ){
					$folder = $_folder[0];
					//echo "Update Folder\n";
				}else{
					$folder = new Folders();
					$folder->path = $path;
					//echo "New Folder\n";
				}
				$stat = stat($path);
				
				$folder->repo = $repo->id;
				$folder->name = $object->getFilename();
				$folder->created = date('Y-m-d H:i:s',$stat['ctime']);
				$folder->updated = date('Y-m-d H:i:s');
				if( $repo_path == dirname($path) ){
					$folder->parent = 0;
				}else{
					$folder->parent = $this->getParentID( dirname($path) );
				}
				//echo "Save Folder: ".print_r($folder->serialize(),1)."\n Stat:".print_r($stat,1)."\n";

				$folder->save();
			}else{
				$_file = Media::find(
					array(
						"path = :path:",
						"bind" => $parameters
					)
				);
	
				$fileInfo = $this->getFileInfo( $path );
				if(!$fileInfo) continue;//couldn't get data, skipping
				
				if( 1 < count($_file)){
					throw new \Exception('To many media matches');
				}elseif(1 == count($_file) ){
					$media = $_file[0];
					//echo "Update Media\n";
					$old_meta = Meta::find(array("media_id = {$media->id}"));
					if($old_meta) foreach($old_meta as $om){
						$om->delete();
					}
				}else{
					$media = new Media();
					$media->path = $path;
					$media->rating = 0;
					//echo "New Media\n";
				}
				$media->created = date('Y-m-d H:i:s',$fileInfo['stat']['ctime']);
				$media->size = $fileInfo['stat']['size'];
				$media->name = $fileInfo['filename'];
				$media->folder_id = $this->getParentID( dirname($path) );
				$media->type = $fileInfo['type'];
				$media->extension = $fileInfo['extension'];
				echo "Save: ".print_r($media->serialize(),1)."\n FileInfo:".print_r($fileInfo,1)."\n";
				$media->save();
				
				foreach($meta_fields as $mf ){
					if(isset($fileInfo[$mf]) && !empty($fileInfo[$mf]) ){
						$meta = new Meta();
						$meta->media_id = $media->id;
						$meta->meta_key = $mf;
						$meta->meta_value = $fileInfo[$mf];
						$meta->save();
					}
				}
				
				if(isset($fileInfo['tags'])  && !empty($fileInfo['tags']) ){
					foreach($tags as $t){
						if( isset($fileInfo['tags'][$t]) && !empty($fileInfo['tags'][$t]) ){
							$meta = new Meta();
							$meta->media_id = $media->id;
							$meta->meta_key = $t;
							$meta->meta_value = $fileInfo['tags'][$t];
							$meta->save();
						}
					}
				}
			}


			//get file type

			
			
		}
	}
	
	function getFileInfo( $path ){
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$path_info = pathinfo($path);
		$path_info['stat'] = stat($path);
		if(isset($path_info['extension'])) $path_info['extension'] = strtolower($path_info['extension']);
		else $path_info['extension'] = '';
		$path_info['path'] = $path;
		$path_info['mime'] = finfo_file($finfo, $path);
		$config = $this->di->get('config');		
		
		switch($path_info['extension']){
			case 'nsv':
			case 'mkv':
			case 'avi':
			case 'm4v':
			case 'mp4':
			case 'divx':
			case 'mpeg':
			case 'mpg':
			case 'm4v':
			case 'div':
			case 'wmv':
				$result = null;
				$info = array();
				$cmd = $config['media']['ffprobe'] . ' -v quiet -print_format json -show_format ' . escapeshellarg($path);
				$json = json_decode(`$cmd`,1);
				if(isset($json['format'])){
					$info = $json['format'];
				}
				$info['type'] = 'movie';
				$info = array_merge($info,$path_info);
				return $info;
			case 'mp3':
			case 'aac':
			case 'wav':
			case 'ogg':
			case 'flac':
				$result = null;
				$info = array();
				$cmd = $config['media']['ffprobe'] . ' -v quiet -print_format json -show_format ' . escapeshellarg($path);
				$json = json_decode(`$cmd`,1);
				if(isset($json['format'])){
					$info = $json['format'];
				}
				$info['type'] = 'song';
				$info = array_merge($info,$path_info);
				return $info;
			case 'pdf':
			case 'chm':
			case 'doc':
				$info = array('type'=>'document');
				$info = array_merge($info,$path_info);
				return $info;
				break;
				
			case 'iso':
				$info = array('type'=>'iso');
				$info = array_merge($info,$path_info);
				return $info;
				break;
						
			case 'srt': //TODO handle subtitles somehow
			case 'idx':
			case 'sub':
			case 'smi':
			case 'sup':
				$info = array('type'=>'subtitle');
				$info = array_merge($info,$path_info);
				return $info;
				break;
			
			case 'jpeg'://folder cover photo
			case 'jpg':
			case 'png':
			case 'gif':
				break;
			
			case 'm3u': //playlist
				break;

			case 'bup': //DVD files
			case 'vob':
			case 'ifo':
				break;
			
			case 'ds_store': //osx useless files
			case ''://no extension
			case 'txt':
			case 'nfo':
			case 'sfv':
			case 'rm':
			case 'db':
			case 'rmvb':
			case 'ini':
				break;
			case 'part': //download files
			case 'rar':
			case 'zip':
			case 'bc!':
			case 'torrent':
				break;
			default:

				//throw new \Exception("Unknown file type: ".$path_info['extension'] . ' @ ' . $path );
		}
		return false;
	}
	
	


}