<?php
namespace Molotov\Modules\SiteManager\Models;


class VirtualHost{
	
	public $fields = array(
		'sslOnly'=>false,
		'user'=>'',
		'group'=>'',
		'fqdn'=>'',
		'aliases'=>'',
		'php_port'=> ''
	);
}