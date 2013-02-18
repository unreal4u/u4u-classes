<?php

/**
 * Chilean RUT or RUN verifier
 *
 * A chilean RUT/RUN is any number between 1.000.000 and 99.999.999, where the first 50 million are reserved for normal
 * persons (RUN - Rol único nacional), and the last 50 millions are reserved for enterprise usage (RUT - Rol único
 * tributario). This number has also a basic verifier that is the last digit in the following secuence:
 * 12.345.678-9
 * So, the above example corresponds to a natural person, with run number 12.345.678 and verifier 9.
 *
 * This class can be used to check whether a RUT or RUN is a valid one, filtering some common invalid RUTs/RUNs out and
 * with the option to add more to this blacklist. Additionally, it is also capable of delivering a basic JavaScript
 * version which will return true or false depending on the RUT/RUN being valid or not. However, this JavaScript version
 * does not check blacklist and other more advanced stuff.
 *
 * @package RUTVerifier
 * @author Camilo Sperberg
 * @copyright 2010 - 2013 Camilo Sperberg
 * @version 1.1
 * @license BSD License
 */
class rutverifier {
    /**
     * Stores errors of the class
     * @var array
     */
    public $errors = array();

    /**
     * Indicates whether we have errors or not
     * @var boolean
     */
    private $error = false;

    /**
     * Stores the already validated emails as a sort of fast cache
     * @var array
     */
    private $validated = array();

    /**
     * Blacklist of all those invalid RUTs
     * @var array
     */
    private $blacklist = array(
        '111111111',
        '222222222',
        '333333333',
        '444444444',
        '555555555',
        '666666666',
        '777777777',
        '888888888',
        '999999999',
    );

    /**
     * This function logs all errors the object generates
     *
     * @param int $type Type of error
     * @param string $msg Error message
     * @return boolean Returns always true
     */
    private function logError($type, $msg='') {
        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $tipo = 'ERR';
                    $this->error = true;
                break;
                case 2:
                    $tipo = 'WAR';
                break;
                case 3:
                    $tipo = 'NOT';
                break;
            }

            $this->errors[] = array(
                'type' => $tipo,
                'msg' => $msg,
            );
        }

        return true;
    }

    /**
     * Allows to add some RUT/RUNs to the blacklist in runtime
     *
     * @param mixed $add List of blacklisted RUT, as an array or a string
     * @return int Returns the number of entries in the blacklist
     */
    public function addToBlacklist($add) {
        if (!empty($add)) {
            if (is_array($add)) {
                foreach ($add AS $a) {
                    $this->blacklist[] = $a;
                }
            } else {
                $this->blacklist[] = $add;
            }
        }

        return count($this->blacklist);
    }

    /**
     * Returns the RUT type: whether it is a person or a juridic entity (companies)
     *
     * This class will return the information in spanish: "empresa" means company, "natural" means regular person. These
     * names are the correct ones to refer to in Chili.
     * Important: This function doesn't verify that the RUT/RUN is valid! For that, check $this->isValidRUT
     * @see $this->isValidRUT()
     *
     * @param string $rut The RUT for which we want to know
     * @return mixed Returns boolean false in case of invalid RUT, array with data otherwise
     */
    public function RUTType($rut='') {
        $output = false;
        if (!empty($rut) AND is_string($rut)) {
            $rut = $this->formatRUT($rut);
            if ($rut !== false) {
                $rut = substr($rut, 0, -1);
                if ($rut < 100000000 AND $rut > 50000000) {
                    $output = array(
                        'e',
                        'empresa',
                    );
                } elseif ($rut > 1000000 AND $rut < 50000000) {
                    $output = array(
                        'n',
                        'natural',
                    );
                }
            }
        }

        return $output;
    }

    /**
     * Applies a filter to the RUT/RUN and formats it for internal class usage
     *
     * @param string $rut The RUT/RUN we want to format
     * @param boolean $withVerifier Whether we want to print the verifier also. Defaults to true
     * @return mixed Returns boolean false when RUT/RUN is invalid, string with the RUT/RUN otherwise
     */
    public function formatRUT($rut='', $withVerifier=true) {
        $output = false;
        if (!empty($rut)) {
            $tmpRUT = preg_replace('/[^0-9kK]/', '', $rut);
            if (strlen($tmpRUT) == 8) {
                $tmpRUT = '0' . $tmpRUT;
            }

            if (strlen($tmpRUT) == 9) {
                $output = str_replace('k', 'K', $tmpRUT);
            } else {
                $this->logError(1, 'RUT/RUN doesn\'t have the required size');
            }

            if ($withVerifier === false AND empty($this->error)) {
                $output = substr($output, 0, -1);
            }
        }

        return $output;
    }

    /**
     * Calculates the verifier for a given RUT/RUN which must be provided without verifier
     *
     * @param string $rut RUT/RUN without verifier
     * @return mixed Returns false if RUT/RUN is empty, or string with verifier otherwise
     */
    public function getVerifier($rut='') {
        $return = false;
        if (!empty($rut) AND is_string($rut)) {
            $multi = 2;
            $sum = 0;
            $strlenRut = strlen($rut);
            for ($i = $strlenRut - 1; $i >= 0; $i--) {
                $sum = $sum + $rut[$i] * $multi;
                if ($multi == 7) {
                    $multi = 2;
                } else {
                    $multi++;
                }
            }
            $rest = $sum % 11;
            if ($rest == 1) {
                $return = 'K';
            } else {
                if ($rest == 0) {
                    $return = '0';
                } else {
                    $return = 11 - $rest;
                }
            }
        }

        return $return;
    }

    /**
     * This function will check whether the RUT/RUN is effectively valid or not
     *
     * @param string $rut RUT/RUN that will be checked
     * @param boolean $extensive_check Whether to also check on blacklist. Defaults to true
     * @param boolean $return_boolean Whether to return true or false or array with data
     * @return mixed Returns boolean true if RUT/RUN is valid, false otherwise. Returns array with data if selected so
     */
    public function isValidRUT($rut, $extensive_check=true, $return_boolean=true) {
        $output = false;
        if (!empty($rut)) {
            if (!empty($this->validated[$rut])) {
                return $this->validated[$rut]['isValid'];
            }

            $rut = $this->formatRUT($rut, true);
            $sep['rut'] = substr($rut, 0, -1);
            $sep['dv'] = substr($rut, -1);

            if ($this->RUTType($rut) !== false) {
                $sep['dvt'] = $this->getVerifier($sep['rut']);
                if ($sep['dvt'] != $sep['dv']) {
                    $this->logError(2, 'RUT/RUN (' . $sep['rut'] . ') and verifier (' . $sep['dv'] . ')  don\'t match');
                } else {
                    $output = true;
                }

                if ($extensive_check === true) {
                    if (in_array($sep['rut'] . $sep['dv'], $this->blacklist)) {
                        $output = false;
                        $this->logError(2, 'The entered RUT/RUN "'.$sep['rut'].$sep['dv'].'" is in blacklist');
                    }
                }
            } else {
                $this->logError(2, 'RUT/RUN isn\'t within range of natural person and/or enterprise');
            }

            $this->validated[$rut] = array(
                'isValid'  => $output,
                'rut'      => $sep['rut'],
                'verifier' => $sep['dv'],
                'type'     => $this->RUTType($rut),
            );
        }
        if ($return_boolean === true) {
            return $output;
        }

        return $this->validated;
    }

    /**
     * Return or prints a JavaScript function that is a bit simpler than its counterpart in PHP to do a basic check
     *
     * @param boolean $echo Whether to echo immediatly from this function. Defaults to false
     * @param boolean $with_headers bool To print the "script" tags. Defaults to false
     */
    public function c_javascript($echo=false, $with_headers=false) {
        $javascript = '';

        if ($with_headers === true) {
            $javascript .= '<script type="text/javascript">';
        }
        $javascript .= 'function rutVerification(c){var r=false,d=c.value,t=d.replace(/\b[^0-9kK]+\b/g,\'\');if(t.length==8){t=0+t;};if(t.length==9){var a=t.substring(t.length-1,-1),b=t.charAt(t.length-1);if(b==\'k\'){b=\'K\'};if(!isNaN(a)){var s=0,m=2,x=\'0\',e=0;for(var i=a.length-1;i>=0;i--){s=s+a.charAt(i)*m;if(m==7){m=2;}else{m++;};}var y=s%11;if(y==1){x=\'K\';}else{if(y==0){x=\'0\';}else{e=11-y;x=e+\'\';};};if(x==b){r=true;c.value=a.substring(0,2)+\'.\'+a.substring(2,5)+\'.\'+a.substring(5,8)+\'-\'+b};}}return r;};';

        if ($with_headers === true) {
            $javascript .= '</script>';
        }

        if ($echo === true) {
            echo $javascript;
        }

        return $javascript;
    }
}
