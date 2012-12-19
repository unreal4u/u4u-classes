<?php
$proc = TRUE; // Create this variable to avoid direct access to parts that your users shouldn't have to.

include('config.php'); // Including the configuration
include('HTMLUtils.class.php'); // Including the main class.

$he = new HTMLUtils(); // Creating the object.

$css[] = array('href' => 'css/base.css','type' => 'css'); // Let's add a CSS.
$css[] = array('href' => 'im/favicon.ico' ,'type' => 'shortcut'); // And a shortcut icon.

$meta[] = array('Author','Camilo Sperberg'); // Also create a meta
$meta[] = array('Copyright','u4u enterprises 2010'); // And another one.

$data_array = array('css' => $css, 'title' => 'Really nice index', 'meta' => $meta);
// Creating a "big" array.
/*
This array could be a lot bigger, but the class only overwrites the options that you specify. 
This would be an example for overriding all possible options:
array(
 'ruleset'         => 'html',
 'ruletype'        => 'strict',
 'additional_info' => FALSE,
 'base_target'     => '_BLANK',
 'css'             => $css,
 'script'          => $scripts, // See below
 'javascript'      => array('function hello(){alert("Hello world!");};','function bye(){alert("Bye world!");};'),
 'title'           => 'Hello World',
 'onload'          => 'document.getElementById("name").focus();',
 'meta'            => $meta 
);
*/
echo $he->c_complete($data_array);
// And printing our first data: all the headers.
/*
There is a second way you can print the headers, which is part by part: 
$he->c_html('html','transitional');
$he->c_link('css/base.css');
$he->c_link('im/favicon.ico','shortcut');
$he->c_script('js/javascript-file.js');
$he->c_javascript('function hello(){alert("Hello world!");}');
$he->c_javascript('function bye(){alert("Bye world!");}');
$he->c_title('This is the title of the document');
$he->c_body('document.getElementById("name").focus();');
// The double quote gets escaped automatically.
*/ 
unset($meta,$css);

echo $he->c_tag('h1','Let\'s try some tags, shall we?','','text-decoration:underline');
// Echoing a h1 tag with style="text-decoration:underline"

$hr = $he->c_tag('hr'); // Let's save this for later.

echo $hr;
echo $he->c_tag('h2','First an external link with a custom class only if the link is external');
$he->href_external_class = 'external-link';
// From now on, all external links will receive this class.
$he->href_external_nofollow = TRUE;
// From now on, all external links will receive rel="nofollow,noindex".
echo $he->c_href('http://www.google.cl','google');
echo $he->c_tag('br').$he->c_href('readme.php','el README de este doc');
// The class will convert readme.php into http://(hostname)/readme.php

echo $hr;
echo $he->c_tag('h2','Now, let\'s include a little flash banner');
echo $he->c_flash('im/banner_fifa_728x90_V3.swf',728,90);
// Passing size and flash location only. Another attributes are id, previous text, post-text, flash version and quality

echo $hr;
echo $he->c_tag('h2','Let\'s print an image now');
echo $he->c_img('im/capturadepantalla201006r.png');
// Printing an image. See how easy this gets? 

echo $hr;
echo $he->c_tag('h2','And what about an "anidated" tag? (p + strong)');
echo $he->c_tag('p',$he->c_tag('b','hello').' '.$he->c_tag('strong','world'));
// As you can see, <b> gets automatically converted into <strong>.

echo $hr;
echo $he->c_tag('h2','Let\'s go for some simple list');

$data_array = array(
 'Item 1',
 $he->c_href('http://www.phpclasses.org/','Item 2'),
 array(
    'Item 3-1',
    'Item 3-2',
    'Item 3-3'),
 $he->c_tag('span','Item 4'));

echo $he->c_list($data_array);
// And that would be all folks!

echo $hr;
echo $he->c_tag('h2','And finally... do we have some errors?');
$he->pre($HTMLErrors);
// pre() prints out by default, no need to echo that. If you don't want pre to print out, use this: 
//echo $he->pre($HTMLErrors,FALSE);

echo $hr;
echo $he->c_javascript('function ge(a){return document.getElementById(a);}');
// We finally include some javascript
echo $he->c_script(array('http://www.google.cl/js/hello.js','js/bye.js'));
// And two scripts.

// No need to explicitely call c_bodyclose() and c_htmlclose() as they are called when the object destroys itself.
// However, let's call just the </body>, as you can see, it won't repeat: 
echo $he->c_closebody();
