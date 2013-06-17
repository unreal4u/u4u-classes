benchmark.class.php
======

This class is deprecated! It is merged with the class "debugInfo" now!
--------

Please refer to this class before going on! This class will soon disappear from the repo.

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com)

About this class
--------

* Can be used to measure time difference between two points.
* Can be used to measure memory difference between two points.

Detailed description
---------

This package can save you some time while benchmarking by maintaining a stack with data such as time and memory.

Basic usage
----------

<pre>include('benchmark.class.php');
$benchmark = new benchmark('bigProcess');
myProcess();
print('Time difference: '.$benchmark->endCounter('bigProcess'));
</pre>

* Congratulations! The benchmark class holds now information about the time and memory it took to process "myProcess()".
* Please see examples for more options and advanced usage

Pending
---------
* Pass an array with names to begin or end those at the same time

Version History
----------

* 0.1:
    * Created class

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
