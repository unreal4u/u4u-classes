<?php if(!isset($proc)) header('Location: ../');
/*
$Rev: 441 $
$Date: 2011-01-14 15:44:24 -0300 (Fri, 14 Jan 2011) $
$Author: unreal4u $
*/
/**
 * Class que se encarga de poder separar páginas
 * @package Internals
 * @author unreal4u
 * @version 1.1
 * @uses Depends on the following classes: HTML Utilities and Extended MySQLi
 */

class paginator {
  public  $per_page;
  public  $first   = 'Primera';
  public  $last    = '&Uacute;ltima';
  public  $page    = 'P&aacute;gina #';
  private $pagedel = 'page';
  private $r;

  /**
   * Función con la que inicializo cuántos elementos por página se deben mostrar
   * @param int $how_many Cuantos elementos por página. Predeterminado: 25
   * @return bool Siempre se devolverá TRUE
   */
  public function __construct($how_many = 25,$page_delimiter = 'page') {
    global $r;
    $this->r = $r;
    if ($how_many != 25) $this->per_page = $how_many;
    else $this->per_page = 25;
    $this->pagedel = $page_delimiter;
    if (!isset($_GET[$this->pagedel])) $_GET[$this->pagedel] = 1;
    $this->r['css']->add(INCL.'themes/'.$r['options']['active_theme'].'/css/paginator.css');
    return TRUE;
  }

  /**
   * Función que construye el HTML necesario para el índice de páginas
   * @param int $total_records Una sumatoria del total de los registros para crear el cálculo de cuántas páginas se necesitan
   * @param string $base_url La URL base sobre la cual construir los links
   * @return array Un arreglo conteniendo toda la información, de esta forma: array('html' => {<div con paginas>}, 'offset' => {offset}, 'limit' => {registros por pagina}, 'current' => '&page={pagina_actual}') 
   */
  public function c_html($total_records = 0,$base_url = '') {
    $output = '';
    $min = 1;
    if (!empty($total_records)) {
      $max = ceil($total_records / $this->per_page);
      if ($_GET[$this->pagedel] > $max AND $max != 0) $this->r['misc']->redir($base_url.$max.'/');
      if ($min < $max) {
        $output .= '<div class="paginador"><div>';
        $num = $max;
        if ($_GET[$this->pagedel] > 5) $offset = $_GET[$this->pagedel] - 4;
        else $offset = 1;
        $min = $offset;
        if ($_GET[$this->pagedel] == $min) $class = 'current-page';
        else $class = '';
        $output .= $this->r['he']->c_href($base_url.'1/',$this->first,'Ir a primera p&aacute;gina',$class);
        if ($num > 10) $num = 9 + $offset;
        if ($num > $max) {
          $min = $max - 9;
          $num = $max;
        }
        for ($i = $min; $i <= $num; $i++) {
          if ($i == $_GET[$this->pagedel]) $class = 'current-page';
          else $class = '';
          $output .= $this->r['he']->c_href($base_url.$i.'/',$i,$this->page.$i,$class);
        }
        if ($_GET[$this->pagedel] == $max) $class = 'current-page';
        else $class = '';
        $output .= $this->r['he']->c_href($base_url.$max.'/',$this->last,'Ir a &uacute;ltima p&aacute;gina',$class);
        unset($offset,$num,$style,$i,$min,$max);
        $output .= '</div></div>';
      }
    }
    return array('html' => $output, 'offset' => (($_GET[$this->pagedel] - 1) * $this->per_page), 'limit' => $this->per_page, 'current' => $_GET[$this->pagedel]);
  }
}
