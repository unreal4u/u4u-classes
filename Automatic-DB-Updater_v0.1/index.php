<?php //if(!isset($proc)) header('Location: ../../');
/**
 * Descripcion del modulo
 * 
 * @package General
 * @version $Rev$
 * @copyright $Date$
 * @author $Author$
 */

$proc = TRUE;
include('config.php');
include('db_updater.class.php');

$db_updater = new db_updater();
