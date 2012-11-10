<?php 
$q = $_SERVER['REQUEST_TIME'] + microtime();
$proc = TRUE;
// Include all involved files
include('config.php');
include('css.class.php');

// Creating object
$theCSS = new CSStacker();

// OPTIONAL: if you want to include a CSS reset, set this to true
// Otherwise, just comment it or delete it
$theCSS->resetCSS = TRUE;

// Add CSS's to the stack like this: 
//$theCSS->add('xd.css');
$theCSS->add('css/lol.css');
//
// OR:
//
//$theCSS->add(array('narf.css','xd.css','css/rolfmao.css','css/lol.css'));
//
// OR: 
//$loadThis[] = 'narf.css';
//$loadThis[] = 'lol.css';
//$theCSS->add($loadThis);


//      Now let's print it, it accepts two parameters and three variables in the first parameter
//      Possible variables in first parameter: "inline", "file" or "filename", defaults to file
$theCSS->printme();
//   This will use the advanced built-in and this may output an 304 not modified header when
//   the client already haves our CSS or it could just print-out the CSS. 

//$theCSS->printme('inline');
//   This will print an inline CSS. You can use this method inside your index instead of having it 
//   in a separate file, making it easier for you to build a CSS stack. However, be aware that the
//   class will not be able to use compression or to use the 304 not modified header.

//$cssfile = $theCSS->printme('filename');
//if ($cssfile !== FALSE) echo $cssfile;
//   This will only create the CSS cache file. If succesfull, it will return the cache file location
//   If unsuccessfull, it will return a FALSE value.
//   As with the inline printing, you can use this method within your index instead of having it
//   in a separate file, making it easier for you to build a CSS stack. However, be aware that the 
//   class will not be able to use compression or to use the 304 not modified header. 

//      Now let's take a look at the second parameter. 
//      The only possible variable in the second parameter is "force". Defaults to FALSE.
//      What this second parameter does, is to force the creation of a cache file, even if no 
//      modifications have been done to the separate CSS files.
//$theCSS->printme(any_of_the_above,'force');
//   Please note that "any_of_the_above" must be replaced with "file", "filename" or "inline".

// OPTIONAL: in case of any error, let's log to a file: 
if(isset($CSSErrors)) {
  $fp = fopen(CACHE_LOCATION.'css-problems.log','a');
  foreach($CSSErrors AS $e) 
    fwrite($fp,$e['type'].' -- '.$e['errm']."\n");
  fclose($fp);
  unset($CSSErrors);
}
unset($theCSS);

// OPTIONAL: let's log the time it took to create the object and do all the work.
file_put_contents(CACHE_LOCATION.'css-problems.log','Generated/processed all the CSS\'s in '.number_format((time()+microtime()) - $q,5,',','.').' seconds'."\n",FILE_APPEND);
?>
