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
        $mapValues[] = array(array(123456789, 123456780), 11);

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
        $mapValues[] = array('30.686.957-4', true, '306869574');
        $mapValues[] = array('30.686.957-4', false, '30686957');
        $mapValues[] = array('306869574', true, '306869574');
        $mapValues[] = array('306869574', false, '30686957');
        $mapValues[] = array('30686957-4', true, '306869574');
        $mapValues[] = array('30686957-4', false, '30686957');
        $mapValues[] = array('30.686.9574', true, '306869574');
        $mapValues[] = array('30.686.9574', false, '30686957');
        $mapValues[] = array('30686.957-4', true, '306869574');
        $mapValues[] = array('30686.957-4', false, '30686957');
        $mapValues[] = array('30.686957-4', true, '306869574');
        $mapValues[] = array('30.686957-4', false, '30686957');
        $mapValues[] = array('3.686.957-4', true, '036869574');
        $mapValues[] = array('3.686.957-4', false, '03686957');
        $mapValues[] = array('36869574', true, '036869574');
        $mapValues[] = array('36869574', false, '03686957');
        $mapValues[] = array('3686957-4', true, '036869574');
        $mapValues[] = array('3686957-4', false, '03686957');
        $mapValues[] = array('3.686.9574', true, '036869574');
        $mapValues[] = array('3.686.9574', false, '03686957');
        $mapValues[] = array('3686.957-4', true, '036869574');
        $mapValues[] = array('3686.957-4', false, '03686957');
        $mapValues[] = array('3.686957-4', true, '036869574');
        $mapValues[] = array('3.686957-4', false, '03686957');
        $mapValues[] = array('', true, false);
        $mapValues[] = array('', false, false);
        $mapValues[] = array(true, true, false);
        $mapValues[] = array(true, false, false);
        $mapValues[] = array(false, true, false);
        $mapValues[] = array(false, false, false);
        $mapValues[] = array(null, true, false);
        $mapValues[] = array(null, false, false);
        $mapValues[] = array(array(), true, false);
        $mapValues[] = array(123456, false, false);
        $mapValues[] = array(123.456, true, false);
        $mapValues[] = array(123.456, false, false);

        return $mapValues;
    }

    /**
     * Tests rutverifier->formatRUT()
     *
     * @dataProvider provider_formatRUT
     *
     * @param string $rut
     * @param boolean $con_dv
     * @param mixed $expected
     */
    public function test_formatRUT($rut='', $con_dv=true, $expected) {
        $result = $this->rutverifier->formatRUT($rut, $con_dv);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Data provider for test_RUTType
     * @return array
     */
    public function provider_RUTType() {
        $mapValues[] = array('11.111.111-1', array('n', 'natural'));
        $mapValues[] = array('77.777.777-7', array('e', 'empresa'));

        return $mapValues;
    }

    /**
     * Tests rutverifier->RUTType
     *
     * @depends test_formatRUT
     * @dataProvider provider_RUTType
     */
    public function test_RUTType($rut='', $expected='') {
        $result = $this->rutverifier->RUTType($rut);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for test_getVerifier()
     */
    public function provider_getVerifier() {
        $mapValues[] = array('30686957', '4');
        $mapValues[] = array('11111112', 'K');
        $mapValues[] = array('', false);
        $mapValues[] = array(false, false);
        $mapValues[] = array(1, false);
        $mapValues[] = array(0, false);
        $mapValues[] = array(true, false);
        $mapValues[] = array(array(), false);


        return $mapValues;
    }

    /**
     * Tests rutverifier->getVerifier
     *
     * @dataProvider provider_getVerifier()
     */
    public function test_getVerifier($rut='', $expected) {
        $result = $this->rutverifier->getVerifier($rut);
        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Data provider for test_isValidRUT
     * @return array
     */
    public function provider_isValidRUT() {
        $mapValues[] = array('306869574', true, true, true);
        $mapValues[] = array('306869570', true, true, false);

        return $mapValues;
    }

    /**
     * Tests rutverifier->isValidRUT()
     *
     * @dataProvider provider_isValidRUT
     * @depends test_formatRUT
     * @depends test_getVerifier
     * @depends test_RUTType
     *
     * @param string $rut
     * @param boolean $extensive_check
     * @param boolean $return_boolean
     * @param mixed $expected
     */
    public function test_isValidRUT($rut, $extensive_check=true, $return_boolean=true, $expected) {
        $result = $this->rutverifier->isValidRUT($rut, $extensive_check, $return_boolean);
        $this->assertEquals($expected, $result);
    }

    public function test_c_javascript() {
        $result = $this->rutverifier->c_javascript(false, false);
        $this->assertStringStartsWith('function rutVerification(c){', $result);

        $result = $this->rutverifier->c_javascript(false, true);
        $this->assertStringStartsWith('<script type="text/javascript">function rutVerification(c){', $result);
    }
}

