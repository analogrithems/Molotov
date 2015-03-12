<?php
namespace Arez\Core\Lib;
	

use Phalcon\Db\Profiler,
    Phalcon\Logger,
    Phalcon\Logger\Adapter\File;

class DatabaseProfiler
{

    protected $_profiler;

    protected $_logger;

    /**
     * Creates the profiler and starts the logging
     */
    public function __construct()
    {
        $this->_profiler = new Profiler();
        $this->_logger = \Phalcon\DI::getDefault()->get('logger');
    }

    /**
     * This is executed if the event triggered is 'beforeQuery'
     */
    public function beforeQuery($event, $connection)
    {
        $this->_profiler->startProfile($connection->getSQLStatement());
    }

    /**
     * This is executed if the event triggered is 'afterQuery'
     */
    public function afterQuery($event, $connection)
    {
        $this->_profiler->stopProfile();
        
	    $profile = $this->_profiler->getLastProfile();
	    if( preg_match('/^select/i', $connection->getSQLStatement()) ){
	        $this->_logger->log($connection->getSQLStatement()."\nTotal Elapsed Time: ". $profile->getTotalElapsedSeconds(), Logger::INFO);
	    }else{
	        $this->_logger->log($connection->getSQLStatement()."\nRows affected:".$connection->affectedRows()."\nTotal Elapsed Time: ". $profile->getTotalElapsedSeconds(), Logger::WARNING);	    
	    }

    }

    public function getProfiler()
    {
        return $this->_profiler;
    }

}