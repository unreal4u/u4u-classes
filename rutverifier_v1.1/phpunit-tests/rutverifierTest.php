<?php
require_once '../rutverifier.class.php';
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * rutverifier test case.
 */
class rutverifierTest extends PHPUnit_Framework_TestCase {
    /**
     * @var rutverifier
     */
    private $rutverifier;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->rutverifier = new rutverifier();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->rutverifier = null;
        parent::tearDown();
    }

    /**
     * Data provider for test_addToBlacklist()
     *
     * @return array
     */
    public function provider_addToBlacklist() {
        $mapValues[] = array(123456789, 10);
        $mapValues[] = array(array(123456780, 123456781), 11);

        return $mapValues;
    }

    /**
     * Tests rutverifier->addToBlacklist()
     *
     * @dataProvider provider_addToBlacklist
     */
    public function test_addToBlacklist($rut, $expected) {
        $result = $this->rutverifier->addToBlacklist($rut);

        // Fix to make private property accesible
        $reflector = new ReflectionProperty('rutverifier', 'blacklist');
        $reflector->setAccessible(true);
        $elements = $reflector->getValue($this->rutverifier);

        $this->assertTrue(count($elements) == $expected);
        $this->assertTrue($result == $expected);
        if (is_array($rut)) {
            foreach($rut AS $r) {
                $this->assertContains($r, $elements);
            }
        } else {
            $this->assertContains($rut, $elements);
        }
    }

    /**
     * Data provider for test_formatRUT
     *
     * @return array
     */
    public function provider_formatRUT() {
        $mapValues[] = array('14.609.732-4', true, '146097324');
        $mapValues[] = array('14.609.732-4', false, '14609732');
        $mapValues[] = array('146097324', true, '146097324');
        $mapValues[] = array('146097324', false, '14609732');
        $mapValues[] = array('14609732-4', true, '146097324');
        $mapValues[] = array('14609732-4', false, '14609732');
        $mapValues[] = array('14.609.7324', true, '146097324');
        $mapValues[] = array('14.609.7324', false, '14609732');
        $mapValues[] = array('14609.732-4', true, '146097324');
        $mapValues[] = array('14609.732-4', false, '14609732');
        $mapValues[] = array('14.609732-4', true, '146097324');
        $mapValues[] = array('14.609732-4', false, '14609732');
        $mapValues[] = array('4.609.732-4', true, '046097324');
        $mapValues[] = array('4.609.732-4', false, '04609732');
        $mapValues[] = array('46097324', true, '046097324');
        $mapValues[] = array('46097324', false, '04609732');
        $mapValues[] = array('4609732-4', true, '046097324');
        $mapValues[] = array('4609732-4', false, '04609732');
        $mapValues[] = array('4.609.7324', true, '046097324');
        $mapValues[] = array('4.609.7324', false, '04609732');
        $mapValues[] = array('4609.732-4', true, '046097324');
        $mapValues[] = array('4609.732-4', false, '04609732');
        $mapValues[] = array('4.609732-4', true, '046097324');
        $mapValues[] = array('4.609732-4', false, '04609732');

        return $mapValues;
    }

    /**
     * Tests rutverifier->formatRUT()
     *
     * @dataProvider provider_formatRUT
     * @param unknown_type $rut
     * @param unknown_type $con_dv
     * @param unknown_type $expected
     */
    public function test_formatRUT($rut='', $con_dv=true, $expected) {
        $result = $this->rutverifier->formatRUT($rut, $con_dv);

        $this->assertEquals($expected, $result);
    }

}

