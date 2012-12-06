<?php

/**
 * Chilean RUT or RUN verifier
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
    public $errores = array();

    /**
     * Indicates whether we have errors or not
     * @var boolean
     */
    private $error = false;

    /**
     * Stores the already validated emails as a sort of cache
     * @var array
     */
    private $validados = array();

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

            $this->errores[] = array(
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
     * names are the correct ones to refer to in Chili
     *
     * @param string $rut The RUT for which we want to know
     * @return mixed Returns boolean false in case of invalid RUT, array with data otherwise
     */
    public function RUTType($rut='') {
        $output = false;
        if (!empty($rut)) {
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
     * Applies a filter to the RUT and formats it according to chilean standards
     *
     * @param string $rut The RUT we want to format
     * @param boolean $withVerifier Whether we want to print the verifier also. Defaults to true
     * @return mixed Returns boolean false when RUT is invalid, string with the RUT otherwise
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
                $this->logError(1, 'RUT no cuenta con el tama&ntilde;o requerido');
            }

            if ($withVerifier === false) {
                $output = substr($output, 0, -1);
            }
        }

        return $output;
    }

    /**
     * Calcula cuál es el dígito verificador para un RUT dado (sin dv)
     *
     * @param $rut string RUT sin dígito verificador
     * @return mixed string OR bool - FALSE en caso de RUT vacío, string con dv en caso contrario
     */
    public function getVerifier($rut='') {
        $dvt = false;
        if (!empty($rut)) {
            $multi = 2;
            $suma = 0;
            for ($i = strlen($rut) - 1; $i >= 0; $i--) {
                $suma = $suma + $rut[$i] * $multi;
                if ($multi == 7) {
                    $multi = 2;
                } else {
                    $multi++;
                }
            }
            $resto = $suma % 11;
            if ($resto == 1) {
                $dvt = 'K';
            } else {
                if ($resto == 0) {
                    $dvt = '0';
                } else {
                    $dvt = 11 - $resto;
                }
            }
        }

        return $dvt;
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
            if (!empty($this->validados[$rut])) {
                return $this->validados[$rut]['valid'];
            }

            $rut = $this->formatRUT($rut, true);
            $sep['rut'] = substr($rut, 0, -1);
            $sep['dv'] = substr($rut, -1);

            if ($this->RUTType($rut) !== false) {
                if (!is_numeric($sep['rut'])) {
                    $this->logError(1, 'RUT no es num&eacute;rico');
                }

                if (!$this->error) {
                    $sep['dvt'] = $this->getVerifier($sep['rut']);
                    if ($sep['dvt'] != $sep['dv']) {
                        $this->logError(2, 'El RUT (' . $sep['rut'] . ') y el d&iacute;gito verificador (' . $sep['dv'] . ') no coinciden');
                    } else {
                        $output = true;
                    }

                    if ($extensive_check === true) {
                        if (in_array($sep['rut'] . $sep['dv'], $this->blacklist)) {
                            $output = false;
                            $this->logError(2, 'El RUT est&aacute; ingresado en blacklist');
                        }
                    }
                }
            } else {
                $this->logError(2, 'El RUT no est&aacute; dentro del rango aceptable');
            }

            $this->validados[$rut] = array(
                'valid' => $output,
                'rut'   => $sep['rut'],
                'dv'    => $sep['dv'],
                'tipo'  => $this->RUTType($rut),
            );
        }
        if ($return_boolean === true) {
            return $output;
        }

        return $this->validados;
    }

    /**
     * Retorna una función en JavaScript, más simple que su simil en PHP para verificar que el RUT está ingresado
     * correctamente o no.
     *
     * @param $echo bool Para que se imprima la cadena inmediatamente dentro de la función
     * @param $with_headers bool Para que se imprima la cadena con sus respectivas cabeceras.
     */
    public function c_javascript($echo=false, $with_headers=false) {
        $javascript = '';

        if ($with_headers === true) {
            $javascript .= '<script type="text/javascript">';
        }
        $javascript .= 'function vr(c){var r=false,d=c.value,t=d.replace(/\b[^0-9kK]+\b/g,\'\');if(t.length==8){t=0+t;};if(t.length==9){var a=t.substring(t.length-1,-1),b=t.charAt(t.length-1);if(b==\'k\'){b=\'K\'};if(!isNaN(a)){var s=0,m=2,x=\'0\',e=0;for(var i=a.length-1;i>=0;i--){s=s+a.charAt(i)*m;if(m==7){m=2;}else{m++;};}var y=s%11;if(y==1){x=\'K\';}else{if(y==0){x=\'0\';}else{e=11-y;x=e+\'\';};};if(x==b){r=true;c.value=a.substring(0,2)+\'.\'+a.substring(2,5)+\'.\'+a.substring(5,8)+\'-\'+b};}}return r;};';

        if ($with_headers === true) {
            $javascript .= '</script>';
        }

        if ($echo === true) {
            echo $javascript;
        }

        return $javascript;
    }
}
