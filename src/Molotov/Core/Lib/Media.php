<?php
namespace Arez\Core\Lib;


class Media {
	public static function get_img_url( $token = null, $height = null, $width = null){
		$di = \Phalcon\DI::getDefault();
		
		if( is_null($token) ) {
			$token = 'notfoundimage';
		}
		
		$media_server = 'https://media.activityrez.com';
		if( 'dev' === $di->get('config')->mode ){
			$media_server = 'https://devmedia.activityrez.com';	
		}
		$route = '/display';
		if( !is_null($height) && is_null($width) ){
			$route = "/thumbnail/height/{$height}";
		}elseif( !is_null($width) && is_null($height) ){
			$route = "/thumbnail/width/{$width}";
		}elseif( !is_null($height) && !is_null($width) ){
			$route = "/thumbnail/{$width}/{$height}";
		}
		$url = $media_server.'/media/'.$token.$route;
		
		return $url;
	}	
}