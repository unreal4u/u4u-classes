<?php if(!isset($proc)) header('Location: ../');
/*
** $Rev: 439 $
** $Date: 2011-01-14 13:18:16 -0300 (Fri, 14 Jan 2011) $
** $Author: unreal4u $
** Description: Allows to verify a chilean RUT or RUN.
*/
/**
 * Chilean RUT or RUN verifier
 * 
 * @package Misc
 * @author Camilo Sperberg
 * @copyright 2010 - 2012 Camilo Sperberg
 * @version 1.0.1
 * @license BSD License
 */
class RUT {

  /**
   * Stores errors of the class
   * 
   * @var array $errores
   */
  public  $errores = array();
  /**
   * Indica de forma interna si ocurrió un error fatal o no
   * @var bool $error
   */
  private $error = FALSE;
    /**
   * Almacena los emails ya validados como una forma de cache
   * @var array $validados
   */
  private $validados = array();
    /**
   * Almacena los RUT que no son válidos
   * @var array $blacklist
   */
  private $blacklist = array('111111111','22222222','33333333','444444444','555555555','666666666','777777777','888888888','999999999');
  
/** 
 * Función que logea los errores que se pudieran producir
 * @param int - type of error
 * @param string - error message
 * @return bool TRUE
 */
  private function logError($type,$msg='') {
    if (!empty($type)) {
      switch($type) {
        case 1: 
          $tipo = 'ERR';
          $this->error = TRUE;
          break;
        case 2:
          $tipo = 'WAR';
          break;
        case 3:
          $tipo = 'NOT';
          break;
      }
      $this->errores[] = array('type' => $tipo, 'msg' => $msg);
    }
    return TRUE;
  }

/**
 * Permite agregar ciertos RUTs a la blacklist
 * @param mixed $add List of blacklisted RUT.
 * @return bool Always returns TRUE
 */
  public function addToBlacklist($add) {
    if (!empty($add)) {
      if (is_array($add)) foreach($add AS $a) $this->blacklist[] = $a;
      else $this->blacklist[] = $add;
    }
    return TRUE;
  }
  
/**
 * Retorna el tipo de RUT: si es persona natural o jurídica
 * @param string $rut El RUT del que se desea consultar su estado
 * @return mixed Returns bool FALSE en case of invalid RUT, array otherwise
 */  
  public function tipoRUT($rut = '') {
    $output = FALSE;
    if (!empty($rut)) {
      $rut = $this->formatRUT($rut);
      if ($rut !== FALSE) {
        $rut = substr($rut, 0, -1);
        if ($rut < 100000000 AND $rut > 50000000) $output = array('e','empresa');
        elseif ($rut > 1000000 AND $rut < 50000000) $output = array('n','natural');
      }
    }
    return $output;
  }

/**
 * Aplica un filtro general al RUT y luego verifica el tamaño de la cadena,
 * devuelve una cadena con un RUT formateado
 * @param string $rut El RUT que se quiere formatear
 * @param bool $con_dv Si se quiere retornar el RUT con dígito verificar
 * @return mixed string OR bool - FALSE en caso de RUT inválido, string con RUT en caso contrario
 */
  public function formatRUT($rut = '', $con_dv = TRUE) {
    $output = FALSE;
    if(!empty($rut)) {
      $tmpRUT = preg_replace('/[^0-9kK]/','',$rut);
      if (strlen($tmpRUT) == 8) $tmpRUT = '0'.$tmpRUT;
      if (strlen($tmpRUT) == 9) $output = str_replace('k', 'K', $tmpRUT);
      else $this->logError(1,'RUT no cuenta con el tama&ntilde;o requerido');
      if ($con_dv === FALSE) $output = substr($output,0,-1);
    }
    return $output;
  }

/**
 * Calcula cuál es el dígito verificador para un RUT dado (sin dv)
 * @param string $rut RUT sin dígito verificador
 * @return mixed string OR bool - FALSE en caso de RUT vacío, string con dv en caso contrario
 */  
  public function getVerificador($rut = '') {
    $dvt = FALSE;
    if (!empty($rut)) {
      $multi = 2; $suma = 0;
      for ($i=strlen($rut) - 1; $i >= 0; $i--) {
        $suma = $suma + $rut[$i] * $multi;
        if ($multi == 7) $multi = 2;
        else $multi++;
      }
      $resto = $suma % 11;
      if ($resto == 1) $dvt = 'K';
      else {
        if ($resto == 0) $dvt = '0';
        else $dvt = 11 - $resto;
      }
    }
    return $dvt;
  }

/**
 * Esta función hace la magia definitiva: revisa si el RUT es válido o no.
 * @param string $rut RUT a verificar
 * @param bool $extensive_check Si se desea una revisión adicional en el blacklist o no
 * @param bool $return_boolean Si se desea retornar sólo un tipo booleano o un resumen con la info
 * @return mixed bool OR array - TRUE en caso de RUT válido, FALSE en caso contrario, array - Arreglo con todo tipo de información con respecto al RUT consultado
 */
  public function isValidRUT($rut, $extensive_check = FALSE, $return_boolean = TRUE) {
    $output = FALSE;
    if(!empty($rut)) {
      if(!empty($this->validados[$rut])) return $this->validados[$rut]['valid'];
      $rut = $this->formatRUT($rut,TRUE);
      $sep['rut'] = substr($rut, 0, -1);
      $sep['dv']  = substr($rut, -1);
      if ($this->tipoRUT($rut) !== FALSE) {
        if (!is_numeric($sep['rut'])) $this->logError(1,'RUT no es num&eacute;rico');
        if (!$this->error) {
          $sep['dvt'] = $this->getVerificador($sep['rut']);
          if ($sep['dvt'] != $sep['dv']) $this->logError(2,'El RUT ('.$sep['rut'].') y el d&iacute;gito verificador ('.$sep['dv'].') no coinciden');
          else $output = TRUE;
        
          if ($extensive_check === TRUE) {
            if (in_array($sep['rut'].$sep['dv'],$this->blacklist)) {
              $output = FALSE;
              $this->logError(2,'El RUT est&aacute; ingresado en blacklist');
            }
          }
        }
      }
      else $this->logError(2,'El RUT no est&aacute; dentro del rango aceptable');
      $this->validados[$rut] = array('valid' => $output, 'rut' => $sep['rut'], 'dv' => $sep['dv'], 'tipo' => $this->tipoRUT($rut));
    }
    if ($return_boolean === TRUE) return $output;
    else return $this->validados;
  }

/**
 * Retorna una función en JavaScript, más simple que su simil en PHP para 
 * verificar que el RUT está ingresado correctamente o no.
 * @param bool $echo Para que se imprima la cadena inmediatamente dentro de la función
 * @param bool $with_headers Para que se imprima la cadena con sus respectivas cabeceras.
 */
  public function c_javascript($echo = FALSE, $with_headers = FALSE) {
    $javascript = '';
    if ($with_headers === TRUE) $javascript .= '<script type="text/javascript">';
    $javascript .= 'function vr(c){var r=false,d=c.value,t=d.replace(/\b[^0-9kK]+\b/g,\'\');if(t.length==8){t=0+t;};if(t.length==9){var a=t.substring(t.length-1,-1),b=t.charAt(t.length-1);if(b==\'k\'){b=\'K\'};if(!isNaN(a)){var s=0,m=2,x=\'0\',e=0;for(var i=a.length-1;i>=0;i--){s=s+a.charAt(i)*m;if(m==7){m=2;}else{m++;};}var y=s%11;if(y==1){x=\'K\';}else{if(y==0){x=\'0\';}else{e=11-y;x=e+\'\';};};if(x==b){r=true;c.value=a.substring(0,2)+\'.\'+a.substring(2,5)+\'.\'+a.substring(5,8)+\'-\'+b};}}return r;};';
    if ($with_headers === TRUE) $javascript .= '</script>';
    if ($echo === TRUE) echo $javascript;
    return $javascript;
  }
}