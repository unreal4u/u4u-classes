csstacker.class.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com).

About this class
--------

* You can add several CSS to a stack. This class will process them and return one big CSS with comments stripped out and some optimizations made (not minified and non obstructive). It can however, save this big CSS to a file so that you don't have to process all the CSS again in every request.
* It is also capable of working with the browser's cache, sending an 304 Not Modified header when necesary.
* It can return 1 array: it is an error array which will tell you what possible errors have ocurred.
* You can choose between an inline print (ideal for embedded CSS) or file print, which generates a typical CSS file that the browser can cache.
* This class has been thoroughly tested with Xdebug so that no errors are present.

Basic usage
----------

<pre>include('config.php'); // Please see below for explanation
include('css.class.php');
$CSStack = new CSStacker();
$CSStack->add(array('first-css.css','second-css.css'));
$CSStack->printme();
</pre>

* Congratulations! This code will print the CSS of `first-css.css` and `second-css.css` combined and optimized.
* **Please see index.php for more options and advanced usage**

Pending
---------
* v1.5: strip out empty declarations, such as `ul{}`
* v1.4: optimize/benchmark color codes replacement.

What can this package do for me?
----------

First of all, if you are not worried about bandwidth, it would be better to NOT implement this class. It would be faster if you just reference the CSS directly from the HTML.
But, if you are concerned with bandwidth usage, than this class is for you. It can take one or several CSS's, and make one big file of it/them. This will certainly reduce some HTTP requests.
It will also create a cache CSS on your server, because when it creates this cache, it will strip out all unnecesary spaces, such as \n, \r, \t, white spaces, and optionally it will try to do some optimizations:
* It will replace all type of 0px, 0em, 0% to just 0. 0 needs no unit.
* It will try to replace some common color names to their short codes, white becomes #FFF, light grey becomes #CCC, and so on. The only exceptions to this rule are 4 letter codes (it would be the same) and the red color, which is the only one that is shorter in the color name than the color code.
* It will also try to replace all color codes that can be shortened: #DDEE11 becomes #DE1, #111111 becomes #111 and so on.
* It will also eliminate the last <code>;</code> of every declaration. It isn't needed.
* Finally, it will behave differently on the ocasion: whenever possible, it will send an 304 Not Modified header or it will try to gzip the contents to send to the client.

What _can't_ this package do for me?
-------------
There are some things that this package won't do for you:
* Shorten `margin-top:0px;margin-bottom:0px;margin-left:0px;margin-right:0px` to `margin:0`. It will however, shorten to `margin-top:0;margin-bottom:0;margin-left:0;margin-right:0` as explained previously.
* Shorten other type of declarations: font, background, padding, etc. Why not? Basicly, to make sure that your CSS will work always as intended. Also because analyzing this kind of situations will require more processor time, and time is money.

FAQ/Why didn't you...
-----------
Q: Use ETags?
A: Because Expires, Cache-Control and Last-Modified headers are far more standarized and they work well with every browser I know about. Besides, sending an ETag will require more processor time (generating the ETag) and also more bandwidth sending all the necesary headers. (Back and forth)

Q: Installed the ability to add CSS when you create the object?
A: Yes, it would have been nice to do that, but that would have meaned not to be able to add more CSS after that step.

Q: Made it compatible to work with JavaScript also?
A: JavaScript behaves completely different than CSS and is far more complicated than CSS. However, I am planning to make another class that does the same thing as this one, but with JavaScript files. Maybe, and only maybe, I could adapt this class to do that, but I'm not a JavaScript expert.

Q: What method do you suggest?
A: Without any doubt, use the internal cache and also the browser cache. I haven't benchmarked this class yet, but generating the first cache file should be the slowest method. Obviously, reading from the cache file or sending a simple 304 Not Modified header should be the fastests methods.

Q: What happens if I want to update the original CSS files?
A: That is one of the big advantages of this class: you can easily update your original CSS files with your favorite editor and whenever you update them, the class will update the internal cache file. When a client checks for a new CSS definition, the class will automaticly send him the last version. (Which happens whenever the client haves a different version of the internal CSS cache)

Q: Is it a good idea to update the cache file generated by this class?
A: Nopes... Whenever a change is detected in the original CSS files, the class will recreate the internal cache file, so any change you will make to the cache file and not the original files will be lost.

Version History
----------

* v1.0 : 
    * Basic functionality

* v1.1 : 
    * Improved GZIP functionality in case the webserver doesn't compress it already

* v1.2 :
    * function printme() now returns 1 or 0 in case their was any CSS to print out (1) or if no output was made (0).
    * If you have added the same CSS more than once, it will be added to the stack only the first time. After that, it will be ignored. Note: **Order is important!**
    * If you have the same declaration in several CSS files, only the last one will be added. Example:<blockquote>
file1.css:
<pre>body{background:#000;color:#FFFFFF}
h4{font-size:110%}
h3{color:#FFF}
h4{font-size:110%}</pre>
file2.css:
<pre>body{background:#000;color:white}
h4{font-size:80%}
h1{background:#FFF;color:000}</pre>
The result will be:
<pre>h3{color:#FFF}h4{font-size:110%}body{background:#000;color:#FFF}h4{font-size:80%}h1{background:#FFF;color:000}</pre></blockquote>
    * Added new constant: GZIP compression level. Allows you to specify the compression level (as a constant).
    * Fixed little inconsistency when method was inline.

* v1.3 :
    * Complete rewrite of the status function. Now it should be a lot faster than before. On my test computer, the time between creating the object and sending the 304 not modified header decreased from ~0.00077 to ~0.00046 seconds, that's ~25% quicker!
    * Complete revision of the "inline" method, which should be ok now. (Before, when the method was inline, it didn't check the cache file and always ended up creating the CSS, now it first verifies the cache and only when it can't rescue from there, it will compress, optimize and finally print).
    * Added option: reset CSS. Now you can insert the CSS reset just doing <code>$css-&gt;resetCSS = TRUE;</code> before you print. <strong>Please note</strong>: if your cache file is newer than the last modified original CSS file, you must manually delete it! Don't know what CSS reset is? More info in <code>css.class.php</code>. (At the compress() function, line ~200)
    * Added new method: "filename", which prints only the cache filename if created and/or valid. Very useful if you want the Webserver to let decide if he should send an 304 header or not. Also, it makes including the class easier because it can be inserted directly within the main script and not in a separate process-css.php file :) Thanks Jason Davis for this <strong>very</strong> great suggestion!
    * Added a new posibility: to force the creation of a cache file. All you have to do is <code>$css-&gt;printme('(method)','force');</code> and a new cache file will be created.
    * Created a new example file (and extended the existing one A LOT) to reflect all changes.

* v1.4 :
    * New version, better documentation and Git support!
    * Many more things to come

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
