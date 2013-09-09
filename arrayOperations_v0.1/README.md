arrayOperations.class.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com)

About this class
--------

* Originally conceived to be a receiver of all loose array-related functions that I've made throughout the years
* Later also a playground to learn about Iterators and SPL functions (related to arrays)

Detailed description
---------

This package .

Basic usage
----------

<pre>include('arrayOperations.class.php');
$arrayOperations = new arrayOperations();
$result = $arrayOperations->getNextAndPrevious(11, array(1, 3, 5, 6, 8, 11));
</pre>

* Congratulations! The returned array will look very similar to this:
 <pre>
 array (size=3)
  'prev' => int 8
  'next' => boolean false
  'curr' => int 11
 </pre>
* Please see examples and PHPUnit tests for more options and advanced usage

Pending
---------
* Search for more loose functions spattered around my codebase

Version History
----------

* 0.1:
    * Created class

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
