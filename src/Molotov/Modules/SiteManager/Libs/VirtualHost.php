<?php

namespace Molotov\Modules\SiteManager\Libs;

class VirtualHost{
	
	public static function saveConfig( $job = null ){
	    $_virtualHostConfig = $job->getBody();

	    exit(0);
	}
	
	public static function removeConfig( $job = null ){
	    $_virtualHostConfig = $job->getBody();

	    exit(0);
	}
}