<?php if(!isset($proc)) header('Location: ../');
/*
$Rev: 597 $
$Date: 2011-05-31 14:12:22 -0400 (Tue, 31 May 2011) $
$Author: unreal4u $
*/
/**
 * MessageStacker for PHP
 * @package Internals
 * @author Camilo Sperberg
 * @version 1.0.0
 * @copyright 2010 - 2011 Camilo Sperberg http://unreal4u.com/
 * @license BSD License
 */

/**
 * Declaración de la class
 * @author unreal4u
 */
class messageStack {
  /**
   * @var int $iErr Contiene el número de errores en la cola
   */
  public $iErr = 0;
  /**
   * @var string $session_identifier El nombre que va a recibir la sesión
   */
  private $session_identifier;

  /**
   * Función __construct, revisa si en la sesión existen errores que mostrar
   * @return NULL
   */
  public function __construct($session_identifier = 'messages') {
    $this->session_identifier = $session_identifier;
    if (empty($_SESSION[$this->session_identifier][0]) OR !is_array($_SESSION[$this->session_identifier])) $_SESSION[$this->session_identifier] = array();
    $this->iErr = count($_SESSION[$this->session_identifier]);
  }

  /**
   * Agrega un mensaje de error a la cola
   * @param int 	$type Tipo de error; 1 = error, 2 = warning, 3 = success
   * @param string 	$message Mensaje a mostrar al usuario
   * @return bool 	Esta función retorna siempre TRUE
   */
  public function add($type = null,$message = '') {
    global $r;
    $exists = FALSE;
    if ($this->iErr > 0) foreach($_SESSION[$this->session_identifier] AS $m) if ($m['inttype'] == $type AND $m['msg'] == $message) $exists = TRUE;
    if (!is_null($type) AND !empty($message) AND empty($exists)) {
      switch($type) {
        CASE 1 : $_SESSION[$this->session_identifier][] = array('inttype' => $type,'type' => 'error'  , 'pre' => $r['he']->c_img(IMG_ERROR).'&nbsp;',   'msg' => $message, 'post' => ''); break;
        CASE 2 : $_SESSION[$this->session_identifier][] = array('inttype' => $type,'type' => 'warning', 'pre' => $r['he']->c_img(IMG_WARNING).'&nbsp;', 'msg' => $message, 'post' => ''); break;
        CASE 3 : $_SESSION[$this->session_identifier][] = array('inttype' => $type,'type' => 'success', 'pre' => $r['he']->c_img(IMG_SUCCESS).'&nbsp;', 'msg' => $message, 'post' => ''); break;
        DEFAULT: break;
      }
      $this->iErr++;
    }
    return TRUE;
  }
  
  /**
   * Muestra los errores acumulados
   * 
   * @param string 	$the_type Elije qué tipo de errores presentar. Opciones: "all" para todos los errores, "error", "warning" y "success" para los distintos tipos
   * @param boolean $flush Whether to delete the message from the stack or not. Defaults to true
   */
  public function display($the_type='all', $flush=true) {
    if ($this->iErr > 0) {
      if ($the_type != 'all') {
        foreach($_SESSION[$this->session_identifier] AS &$m) {
          if ($m['inttype'] == $the_type) echo '<p class="'.$m['type'].'">'.$m['pre'].$m['msg'].$m['post'].'</p>';
          if ($flush === true) {
          	unset($m);
          }
        }
      }
      else {
        foreach($_SESSION[$this->session_identifier] AS $m) {
          echo '<p class="'.$m['type'].'">'.$m['pre'].$m['msg'].$m['post'].'</p>';
          if ($flush === true) {
          	array_shift($_SESSION[$this->session_identifier]);
          }
        }
      }
    }
  }
}
