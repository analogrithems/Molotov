<?php
/*
 *  This is the ActivityRez queue system used to queue tasks 
 *  in the background to prevent user delay on the front end.
 *  This is very useful for sending emails, running reports,
 *  reindexing search systems etc
 *
 *  When using the queue system you must also run the worker
 *  daemon that handles jobs.  All jobs are automatically prefixed
 *  with the environemnt they run in.  This insures each worker only
 *  processes jobs for it's own environment.  
 *  
 *  For example adding a job to the queue to send an email in the staging
 *  environment will only get processed for the staging worker
 *
 * @author Aaron Collins <aaron@activityrez.com>
 */
namespace Arez\Core\Lib;

use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;

class Queue extends BeanstalkExtended
{
    public function __construct($options = null)
    {
    	//Lets prefix our queue
    	$di = \Phalcon\DI::getDefault();
		$_url = parse_url($di->get('config')->url);
		$prefix = $_url['host'].'_';
    	$options['host'] = $di->get('config')->queue_host;
    	$options['prefix'] = $prefix;
    	$options['logger'] = $di->get('logger');
        parent::__construct($options);
    }

}