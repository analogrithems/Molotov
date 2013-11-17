<?php
namespace Molotov\Core\Tests;

abstract class UnitTestCase extends \Phalcon\Test\UnitTestCase {

    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    /**
     * @var bool
     */
    private $_loaded = false;

    public function setUp() {

        // Load any additional services that might be required during testing
        $di = \Phalcon\DI::getDefault();

        // get any DI components here, if you have a config, be sure to pass it to the parent

        parent::setUp($di);

        $this->_loaded = true;
    }

    /**
     * Check if the test case is setup properly
     * @throws \PHPUnit_Framework_IncompleteTestError;
     */
    public function __destruct() {
        if(!$this->_loaded) {
            throw new \PHPUnit_Framework_IncompleteTestError('Please run parent::setUp().');
        }
    }
}
