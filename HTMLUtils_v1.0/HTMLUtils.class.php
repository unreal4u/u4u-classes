<?php
namespace u4u;

class HTMLUtils {
/*********
Initializing some variables we're going to need.
*********/
  private $ruleset = 'xhtml';
  private $language = 'en';
  private $body_closed = FALSE;
  private $html_closed = FALSE;

  public $fallback_flash_img     = 'im/not-compatible.png';
  public $fallback_flash_msg     = 'We are sorry, but this page uses propietary technology which don\'t allows the normal visualization of this page.';
  public $href_external_class    = '';
  public $href_external_nofollow = FALSE;

/*********
The __construct function, gets and/or sets the language used.
Is public because... well, methods should always be public.
*********/
  public function __construct() {
    if (strlen(setlocale(LC_ALL,0)) < 2) $this->language = setlocale(LC_ALL,LOCALE);
    else $this->language = setlocale(LC_ALL,0);
    header('Content-Type: text/html; charset: '.CHARSET);
  }

  public function __destruct() {
    if ($this->body_closed === FALSE) echo $this->c_closebody();
    if ($this->html_closed === FALSE) echo $this->c_closehtml();
  }
/*********
This function checks whether we're using xhtml or html and is able to establish the
ending tag for each case.
It is a private function and should NOT be used for general public.
*********/
  private function endtag($tag='',$simple_close = FALSE) {
    $output = FALSE;
    if ($simple_close === TRUE) $output .= '</'.$tag.'>';
    else {
      if ($this->ruleset == 'xhtml') $output = ' />';
      elseif (!empty($tag) AND $this->ruleset == 'html') $output = '></'.$tag.'>';
      else $output = '>';
    }
    return $output;
  }

/**********
This function finds out whether the src is an external or internal file. If internal,
it also checks whether it is readable.
**********/
  private function external($src) {
    $src = str_replace(HOME,'',$src);
    $output = array('external'=>FALSE,'href'=>$src,'readable'=>TRUE);
    if (strpos($src,'http://') === FALSE) {
      if (strpos($src,'/') === 0) $src = substr_replace($src,'',0,1);
      if (!is_readable(ABSPATH.$src)) {
        $output['readable'] = FALSE;
        $this->logError(2,'File '.$src.' located in '.ABSPATH.$src.' isn\'t readable or doesn\'t exist');
      }
      $output['href'] = HOME.$src;
    }
    else $output['external'] = TRUE;

    return $output;
  }

/************
This function logs into HTMLErrors, a global array containing all the errors this
class generated during its execution.
************/
  private function logError($type=0,$msg='') {
    global $HTMLErrors;
    if (!empty($type) AND !empty($msg)) {
      switch($type) {
        case 1 : $type_string = 'FATAL';   break;
        case 2 : $type_string = 'WARNING'; break;
        case 3 : $type_string = 'NOTICE';  break;
        default: $type_string = 'UNKNOWN'; break;
      }
      $HTMLErrors[] = array('type' => $type_string, 'msg' => $msg);
    }
  }

/*********
Non-vital function to this class, I just keep it because debugging is a little easier. This
function applies htmlentities so you can print whatever you want. (And display it nicely
on-screen).
It gets two parameters:

@a      : What do you want to print?
@print  : Whether to print or just return. Defaults to print.
*********/
  public function pre($a,$print=TRUE) {
    $output = TRUE;
    if (!is_null($a)) $output = '<pre>'.htmlentities(print_r($a,TRUE)).'</pre>';
    else $output = '<pre>(null)</pre>';
    if ($print === TRUE) echo $output;
    return $output;
  }

/**********
This function creates the basic output of the html tag and the rulesets.

@ruleset : "xhtml" for XHTML 1.0 (Sorry, no support for XHTML 1.1) OR "html" for HTML 4.01.
@ruletype: "transitional" OR "strict". Sorry, no support for frameset.
**********/
  public function c_html($ruleset='xhtml',$ruletype='transitional',$additional_info=TRUE,$base_target=FALSE) {
    $output = '<!DOCTYPE html PUBLIC "-//W3C//DTD ';
    $ruletype = ucwords($ruletype);
    $language = strtoupper(substr($this->language,0,2));
    if ($ruletype == 'Transitional' OR $ruletype == 'Strict') {
      $this->ruleset = $ruleset;
      switch($ruleset) {
        case 'xhtml' :
          $output .= 'XHTML 1.0';
          $url = 'xhtml1/DTD/xhtml1-'.strtolower($ruletype);
          $html = ' lang="'.strtolower($language).'" xmlns="http://www.w3.org/1999/xhtml"';
          break;
        case 'html'  :
          $output .= 'HTML 4.01';
          $url = 'html4/';
          if ($ruletype == 'Transitional') $url .= 'loose';
          else $url .= 'strict';
          $html = '';
          break;
        default      :
          $output = FALSE;
          break;
      }
      $output .= ' '.$ruletype.'//EN"'.PHP_EOL.'"http://www.w3.org/TR/'.$url.'.dtd">'.PHP_EOL;
      $output .= '<html'.$html.'><head>';
      if ($additional_info === TRUE) {
        $output .= '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'"'.$this->endtag('meta');
        $output .= '<base href="'.HOME.'"';
        if ($base_target !== FALSE) $output .= ' target="'.$base_target.'"';
        $output .= $this->endtag('base');
      }
    }
    else $output = FALSE;
    return $output;
  }

/**********
This function creates all necesary output for the "link" tag. I didn't support the hole link tag, only shortcut icon and css.

@href : Location of the shortcut icon or css file
@type : "css" (for a CSS file) OR "shortcut" (for a shortcut icon). Defaults to css.
**********/
  public function c_link($href='',$type='css') {
    $output = '<link rel="';
    $file = $this->external($href);
    if ($file['readable'] === FALSE) $output = FALSE;
    if ($output !== FALSE) {
      if ($type == 'shortcut') $output .= 'shortcut icon';
      elseif ($type == 'css')  $output .= 'stylesheet" type="text/css';
      else $output = FALSE;

      if ($output !== FALSE) $output .= '" href="'.$file['href'].'"'.$this->endtag('link');
    }
    return $output;
  }

/***********
This function allows us to link arbitrary javascript FILES to our document.

@in : string or array with the route to the javascript file(s)
***********/
  public function c_script($in='') {
    $output = FALSE;
    if (is_array($in)) {
      $output = '';
      foreach($in AS $a) {
        $file = $this->external($a);
        if ($file['readable'] === TRUE) $output .= '<script type="text/javascript" src="'.$file['href'].'"></script>';
      }
    }
    else {
      $file = $this->external($in);
      if ($file['readable'] === TRUE) $output = '<script type="text/javascript" src="'.$file['href'].'"></script>';
    }
    return $output;
  }

/************
This function allows us to embed javascript code.

@in : string or array with the javascript. If it is an array, all code will be included in 1 block.
************/
  public function c_javascript($in='') {
    $output = FALSE;
    if (is_array($in)) {
      $script = '';
      foreach($in AS $a) $script .= $a;
    }
    else $script = $in;

    if (!empty($script)) $output = '<script type="text/javascript">'.$script.$this->endtag('script',TRUE);
    return $output;
  }

/**********
This function creates all necesary output for the "title" tag.

@title : The title of the document
**********/
  public function c_title($title='') {
    $output = FALSE;
    if (!empty($title)) $output = '<title>'.$title.'</title>';
    return $output;
  }

/**********
This function closes the "head" tag and then opens the "body" tag.

@onload : jQuery is definitely better, but this old-style onloader is still valid.
**********/
  public function c_body($onload='') {
    $output = '</head><body';
    if (!empty($onload)) $output .= ' onload="javascript:'.str_replace('"','\"',$onload).'"';
    $output .= '>';
    return $output;
  }

/**********
This function creates a "meta" tag.

@name     : Content of the "name" property of the meta tag.
@content  : Content of the "content" property of the meta tag.
**********/
  public function c_meta($name='',$content='') {
    $output = FALSE;
    if (!empty($name) AND !empty($content)) $output = '<meta name="'.$name.'" content="'.$content.'"'.$this->endtag('meta');
    return $output;
  }

/**********
This function allows us to create all header code with just one call and one array.

@data_array : All info for creating our header in just one array set.
**********/
  public function c_complete($data_array=0) {
    $output=TRUE;
    $defaults=array('ruleset'=>'xhtml','ruletype'=>'transitional','additional_info'=>TRUE,'base_target'=>FALSE,'css'=>array(),'script'=>array(),'javascript'=>array(),'title'=>null,'onload'=>null,'meta'=>array());
    if (!isset($data_array) OR empty($data_array) OR !is_array($data_array)) $output = FALSE;
    if ($output) $defaults = array_merge($defaults,$data_array);

    $output  = $this->c_html($defaults['ruleset'],$defaults['ruletype'],$defaults['additional_info'],$defaults['base_target']);
    if (count($defaults['css']) > 0) foreach($defaults['css'] AS $a) $output .= $this->c_link($a['href'],$a['type']);
    if (count($defaults['script']) > 0) foreach($defaults['script'] AS $a) $output .= $this->c_script($a);
    if (count($defaults['javascript']) > 0) $output .= $this->c_javascript($defaults['javascript']);
    if (count($defaults['meta']) > 0) foreach($defaults['meta'] AS $a) $output .= $this->c_meta($a[0],$a[1]);
    $output .= $this->c_title($defaults['title']);
    $output .= $this->c_body($defaults['onload']);
    return $output;
  }

/**********
This function creates an "img" tag, with all necesary sintaxis that finally allows us to create a valid image with very little code.

@ruta      : route to the file. Can be absolute or relative, external or internal.
@alt       : alternative text to display in case the image couldn't be found.
@class     : an optional class to apply to the image.
@style     : an optional style to apply to the image.
@javascript: allows you to attach javascript tags to the image.
@absolute  : whether to return the absolute path or not. Defaults to not.
@width     : specify the width manually (use it always with external files!)
@height    : specigy the height manually (use it always with external files!)
**********/
  public function c_img($ruta='',$alt='',$class='',$style='',$javascript='',$absolute=false,$width=0,$height=0) {
    $file = $this->external($ruta);
    if ($file['readable'] === TRUE) {
      if ($absolute === TRUE) $output = '<img src="'.ABSPATH.$ruta.'" ';
      else $output = '<img src="'.$file['href'].'" ';
      if (empty($alt)) $alt = ' ';
      if($file['readable'] === TRUE AND $file['external'] === FALSE AND empty($width) AND empty($height)) {
        $info_img = getimagesize(ABSPATH.$ruta);
        $output .= $info_img[3];
        unset($info_img);
      }
      else if (!empty($width) AND !empty($height)) $output .= 'width="'.$width.'" height="'.$height.'"';
      if (!empty($class)) $output .= ' class="'.$class.'"';
      if (!empty($style)) $output .= ' style="'.$style.'"';
      if (!empty($javascript)) $output .= ' '.$javascript;
      $output .= ' alt="'.$alt.'" title="'.$alt.'"'.$this->endtag();
    }
    else $output = FALSE;
    return $output;
  }

/**********
This function creates an "a" tag.

@href     : link to what you want to point to.
@tit      : text that will be displayed to user.
@alt      : alternative text.
@class    : a special class that you want to be applied to the link.
@style    : style that you want to be applied to the link.
@target   : whether to open link in new window or not. Defaults to same window.
**********/
  public function c_href($href=HOME,$tit='',$alt='',$class=FALSE,$style=FALSE,$nofollow=FALSE,$noindex=FALSE,$target=FALSE) {
    $output = FALSE;
    if (!empty($tit)) {
      $output = '<a href="';
      $file = $this->external($href);
      $output .= $file['href'].'"';
      $output .= ' title="';
      if (!empty($alt)) $output .= $alt;
      $output .= '"';
      if (!empty($class) OR ($file['external'] === TRUE AND !empty($this->href_external_class))) {
        $output .= ' class="';
        if ($file['external'] === TRUE AND !empty($this->href_external_class)) {
          $output .= $this->href_external_class;
          if (!empty($class)) $output .= ' ';
        }
        if (!empty($class)) $output .= $class;
        $output .= '"';
      }
      if (!empty($style)) $output .= ' style="'.$style.'"';
      if ($target === TRUE) $output .= ' target="_BLANK"';
      if (($nofollow === TRUE OR $noindex === TRUE OR $this->href_external_nofollow === TRUE) AND $file['external'] === TRUE) {
        if ($this->href_external_nofollow === TRUE) {
          $nofollow = TRUE;
          $noindex  = TRUE;
        }
        $output .= ' rel="';
        if ($nofollow === TRUE  AND $noindex === TRUE ) $output .= 'nofollow,noindex';
        if ($nofollow === TRUE  AND $noindex === FALSE) $output .= 'nofollow';
        if ($nofollow === FALSE AND $noindex === TRUE ) $output .= 'noindex';
        $output .= '"';
      }
      $output .= '>'.$tit.$this->endtag('a',TRUE);
    }
    return $output;
  }

/**********
Function that allows us to create the html asociated with it.
Allowed tags:
  <br>
  <hr>
  <p>
  <span>
  <blockquote>
  <h1> - <h6>
  <sub>
  <sup>
  <s>
  <strong> (<b>)
  <em> (<i>)
  <u>
  <pre>
  <code>
**********/
  public function c_tag($tag = '', $content = '', $class = '', $style = '', $id = '', $name = '') {
    $output = FALSE;
    if (!empty($tag)) {
      $tag = strtolower($tag);
      if ($tag == 'b') $tag = 'strong';
      if ($tag == 'i') $tag = 'em';

      $properties = '';
      if (!empty($class)) $properties .= ' class="'.$class.'"';
      if (!empty($style)) $properties .= ' style="'.$style.'"';
      if (!empty($id))    $properties .= ' id="'.$id.'"';
      if (!empty($name))  $properties .= ' name="'.$name.'"';

      switch($tag) {
        case 'p': case 'span': case 'blockquote': case 'h1': case 'h2': case 'h3': case 'h4' : case 'h5': case 'h6': case 'sub': case 'sup': case 's': case 'strong': case 'em': case 'u': case 'pre': case 'code':
          if (!empty($content)) $output = '<'.$tag.$properties.'>'.$content.$this->endtag($tag,TRUE);
          else $output = FALSE;
          break;
        case 'br': case 'hr':
          $output .= '<'.$tag.$properties.$this->endtag($tag);
          break;
       default:
          $this->logError(2,'Sorry, tag "'.$tag.'" not allowed.');
          $output = FALSE;
          break;
      }
    }
    return $output;
  }

  public function c_flash($url='',$width=0,$height=0,$id='',$pre_text='',$post_text='',$flash_version='6,0,40,0',$quality='high') {
    $output = FALSE;
    if (!empty($url) AND !empty($width) AND !empty($height)) {
      $file = $this->external($url);
      $output  = $pre_text.'<!--[if IE]><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
      if (!empty($id)) $output .= ' id="'.$id.'"';
      $output .= ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version='.$flash_version.'" height="'.$height.'" width="'.$width.'">';
      $output .= '<param name="movie" value="'.$file['href'].'"'.$this->endtag('param');
      $output .= '<param name="quality" value="'.$quality.'"'.$this->endtag('param').'<![endif]-->';
      $output .= '<!--[if !IE]> <--><object data="'.$file['href'].'" type="application/x-shockwave-flash" height="'.$height.'" width="'.$width.'"';
      if (!empty($id)) $output .= ' name="'.$id.'"';
      $output .= '><param name="quality" value="'.$quality.'"'.$this->endtag('param').'<param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer"'.$this->endtag('param').'<!-- <![endif]-->';
      if (is_readable(ABSPATH.$this->fallback_flash_img)) $output .= $this->c_img($this->fallback_flash_img,$this->fallback_flash_msg);
      else $output .= $this->fallback_flash_msg;
      $output .= $this->endtag('object',TRUE).$post_text;
    }
    return $output;
  }

/************
This function is able to construct a simple list from an array.

@in      : The array we are going to print.
@id      : The id of the list (Applies only to first list).
@class   : The class of the list (Applies only to first list).
@style   : Style of the list (Applies only to first list).
@li_class: The class of each <li> (Applies only to first list).
@li_style: The style of each <li> (Applies only to first list).
************/
  public function c_list($in='',$id='',$class='',$style='',$li_class='',$li_style='') {
    if (!is_array($in)) $output = FALSE;
    else {
      $output = '<ul';
      if (!empty($id)) $output .= ' id="'.$id.'"';
      if (!empty($class)) $output .= ' class="'.$class.'"';
      if (!empty($style)) $output .= ' style="'.$style.'"';
      $output .= '>';
      $max = count($in);
      for ($i = 0; $i < $max; $i++) {
        if (is_array($in[$i])) $output .= $this->c_list($in[$i]);
        else {
          if ($i != 0) $output .= '</li>';
          $output .= '<li';
          if (!empty($li_class)) $output .= ' class="'.$li_class.'"';
          if (!empty($li_style)) $output .= ' style="'.$li_style.'"';
          $output .= '>';
          $output .= $in[$i];
        }
      }
      $output .= '</li></ul>';
    }
    return $output;
  }

/**********
This function closes the "body" tag.
**********/
  public function c_closebody() {
    $this->body_closed = TRUE;
    return '</body>';
  }

/**********
This function closes the "html" tag.
**********/
  public function c_closehtml() {
    $this->html_closed = TRUE;
    return '</html>';
  }
}
