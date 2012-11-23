<?php
require_once '../cacheManager.class.php';
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * cacheManager test case.
 */
class cacheManagerAPCTest extends PHPUnit_Framework_TestCase {
    /**
     *
     * @var cacheManager
     */
    private $cacheManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->cacheManager = new cacheManager('apc');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->cacheManager = null;
        parent::tearDown();
    }

    public function provider__call() {
        $languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

        $mapValues = array(
            array('load', array('languageMessages', $languageIds), false),
        );

        return $mapValues;
    }
    /**
     * Tests cacheManager->__call()
     *
     * @dataProvider provider__call
     */
    public function test__call($func, $args, $expected) {
        $result = $this->cacheManager->$func($args);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests cacheManager->throwExceptions()
     */
    public function testThrowExceptions() {
        // TODO Auto-generated cacheManagerTestAPC->testThrowExceptions()
        $this->markTestIncomplete("throwExceptions test not implemented");
        $this->cacheManager->throwExceptions(/* parameters */);
    }
}

