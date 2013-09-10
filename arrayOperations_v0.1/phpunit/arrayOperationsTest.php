<?php

namespace u4u;

require_once '../arrayOperations.class.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * arrayOperations test case.
 */
class arrayOperationsTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var arrayOperations
     */
    private $arrayOperations;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->arrayOperations = new \u4u\arrayOperations();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->arrayOperations = null;
        parent::tearDown();
    }

    /**
     * Data provider for test_getNextAndPrevious()
     *
     * @return array
     */
    public function provider_getNextAndPrevious() {
        $bigArray = array(
            127199 => 'a',
            '20037' => 'a',
            '169249' => 'a',
            14333 => 'a',
            171220 => 'a',
        );

        $mapValues[] = array(1,       array(1,3,5,6,7,8,11),   false, array('prev' => false, 'next' => 3,     'curr' => 1));
        $mapValues[] = array(7,       array(1,3,5,6,7,8,11),   false, array('prev' => 6,     'next' => 8,     'curr' => 7));
        $mapValues[] = array(5,       array(1,3,5,6,7,8,11),   false, array('prev' => 3,     'next' => 6,     'curr' => 5));
        $mapValues[] = array(8,       array(1,3,5,6,8,11),     false, array('prev' => 6,     'next' => 11,    'curr' => 8));
        $mapValues[] = array(11,      array(1,3,5,6,8,11),     false, array('prev' => 8,     'next' => false, 'curr' => 11));
        $mapValues[] = array(20,      array(1,3,5,6,8,11),     false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array('a',     array('a', 'b', 'c'),    false, array('prev' => false, 'next' => 'b',   'curr' => 'a'));
        $mapValues[] = array(1,       array(1,2),              false, array('prev' => false, 'next' => 2,     'curr' => 1));
        $mapValues[] = array(2,       array(1,2),              false, array('prev' => 1,     'next' => false, 'curr' => 2));
        $mapValues[] = array(1,       array(1),                false, array('prev' => false, 'next' => false, 'curr' => 1));
        $mapValues[] = array(5,       array(),                 false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(false,   array(),                 false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(array(), array(),                 false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(true,    array(true, false),      false, array('prev' => false, 'next' => false, 'curr' => true));
        $mapValues[] = array(true,    array(true),             false, array('prev' => false, 'next' => false, 'curr' => true));
        $mapValues[] = array(null,    null,                    false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(3,       null,                    false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(array(3),array(array(3), 4),      false, array('prev' => false, 'next' => 4,     'curr' => array(3)));
        $mapValues[] = array(array(3),array(array(3, 4), 5),   false, array('prev' => false, 'next' => false, 'curr' => false));
        $mapValues[] = array(3.1415,  array(3.14,3.1415,3.15), false, array('prev' => 3.14,  'next' => 3.15,  'curr' => 3.1415));

        $mapValues[] = array(3,       array(1 => array(1), 3 => array(1), 4 => array(1)), true, array('prev' => 1, 'next' => 4, 'curr' => 3));
        $mapValues[] = array(1,       array(1 => array(1), 3 => array(1), 4 => array(1)), true, array('prev' => false, 'next' => 3, 'curr' => 1));
        $mapValues[] = array(4,       array(1 => array(1), 3 => array(1), 4 => array(1)), true, array('prev' => 3, 'next' => false, 'curr' => 4));
        $mapValues[] = array(127199,  $bigArray, true, array('prev' => false,  'next' => 20037,  'curr' => 127199));

        $mapValues[] = array(20037,   $bigArray, true, array('prev' => 127199, 'next' => '169249', 'curr' => '20037'));
        $mapValues[] = array('20037', $bigArray, true, array('prev' => 127199, 'next' => '169249', 'curr' => '20037'));
        $mapValues[] = array(171220,  $bigArray, true, array('prev' => 14333,  'next' => false,    'curr' => 171220));

        return $mapValues;
    }

    /**
     * Tests arrayOperations->getNextAndPrevious()
     *
     * @dataProvider provider_getNextAndPrevious
     */
    public function test_getNextAndPrevious($id, $arrayValues, $keyBased, $expected) {
        $result = $this->arrayOperations->getNextAndPrevious($id, $arrayValues, $keyBased);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Data provider for test_hasNotEmptyValues()
     *
     * @return array
     */
    public function provider_hasEmptyValues() {
        $mapValues[] = array(array(1,2,3), false, false);
        $mapValues[] = array(array(false, null, 0, ''), false, true);
        $mapValues[] = array(array(1, null, 3), false, true);
        $mapValues[] = array(array(), false, true);
        $mapValues[] = array(1, false, true);
        $mapValues[] = array(null, false, true);
        $mapValues[] = array(1, true, true);
        $mapValues[] = array(null, true, true);
        $mapValues[] = array(array(1,2 => array(4,5,6), 3), true, false);
        $mapValues[] = array(array(1,2 => array(4,0,6), 3), true, true);
        $mapValues[] = array(array(0,2 => array(4,5,6), 3), true, true);
        $mapValues[] = array(array(1,2 => array(4,5,6), 3), false, false);
        $mapValues[] = array(array(1,2 => array(4,0,6), 3), false, false);
        $mapValues[] = array(array(0,2 => array(4,5,6), 3), false, true);
        $mapValues[] = array(array(1,2 => array(0,0,0), 0), false, true);
        $mapValues[] = array(array(1,2 => array(0,0,0), 3), false, false);
        // Edge cases: empty array() is considered not empty but a not empty array with empty values within it is
        $mapValues[] = array(array(1,2 => array(), 3), false, true);
        $mapValues[] = array(array(1,2 => array(0), 3), false, false);

        return $mapValues;
    }

    /**
     * Tests arrayOperations->hasEmptyValues()
     *
     * @dataProvider provider_hasEmptyValues
     */
    public function test_hasEmptyValues($array, $recursive, $expected) {
        $result = $this->arrayOperations->hasEmptyValues($array, $recursive);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }
}