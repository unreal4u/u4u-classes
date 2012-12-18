<html><head><title>test</title>
<?php
$proc = TRUE;
include('config.php');
include('csstacker.class.php');
$theCSS = new CSStacker();
$theCSS->add('examples/css/uncompressed.css');

echo '<link rel="stylesheet" type="text/css" href="'.$theCSS->printme('filename').'" />';
// Please note that with this method you won't be able to use the built-in header modifications features!
unset($theCSS);
?>
</head>
<body><p>Hello world!</p>
<p>This is an example of how to use the filename and inline options within your index.php file</p>
<p>As you can see, there isn't much difference</p></body></html>
