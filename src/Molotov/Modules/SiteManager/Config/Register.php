<?php

/* SiteManager module autoloader defs */
$config['namespaces']['Molotov\Modules\SiteManager\Models'] = MODULES_DIR."/SiteManager/Models";
$config['namespaces']['Molotov\Modules\SiteManager\Libs'] = MODULES_DIR."/SiteManager/Libs";
$config['namespaces']['Molotov\Modules\SiteManager\Controllers'] = MODULES_DIR."/SiteManager/Controllers";
$config['namespaces']['Molotov\Modules\SiteManager\Tests\Controllers'] = MODULES_DIR."/SiteManager/Test/Controllers";


$di = \Phalcon\DI::getDefault();

//create web virtual host
$di->get('queue')->addWorker('addVirtualHost','Molotov\Modules\SiteManager\Libs\VirtualHost::saveConfig');
$di->get('queue')->addWorker('delVirtualHost','Molotov\Modules\SiteManager\Libs\VirtualHost::removeConfig');
