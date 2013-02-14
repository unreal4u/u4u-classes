README
==============

u4u-classes
--------------

u4u-classes is a compilation of classes of unreal4u (Camilo Sperberg) available to general public and usage. Feel free
to use and modify anything in these classes you want. They have all [BSD licenses](http://en.wikipedia.org/wiki/BSD_licenses).

Disclaimer
--------------

I'll always will try to maintain basic operation within my classes and ensure that they will do the job they say it does,
but use these classes at your own risk! Most of them have been tested (some with PHPUnit test cases) and are already
being used in production websites, but my way of implementing the classes may differ from what you are doing, and in
those cases, bugs can originate without me knowing about them. If you want to use my classes, you are free to do so, but
it is your responsability to test them thoroughly to ensure that they will work in your case scenario!

Classes description
--------------

* *Automatic-DB-Updater_v0.1*: Outdated class. Will not be updated anymore.
* *benchmark_v0.1*: Outdated class. See benchmark_v0.2
* *benchmark_v0.2*: This class can be used to measure times of several variables at the same time. It will also log
  memory usage for those same variables.
* *cacheManager_v2.0*: Outdated class. See cacheManager_v2.1
* *cacheManager_v2.1*: Outdated class. See cacheManager_v2.2
* *cacheManager_v2.2*: This class will enable you to use APC or file based cache module based on a name and a few
  arguments. Changing between these cache types is just a matter of changing the constructor call, from
  `new cacheManager('apc');` to `new cacheManager('your-type');`. You can additionally write your own wrappers, just
  implement the interface and extend the base class and you're good to go!
* *CSStacker_v1.3*: Outdated class. See CSStacker v1.4.
* *CSStacker_v1.4*: A little more updated class. Will be updated soon.
* *db-mysqli_v3.1.1*: (Very) outdated class. See db-mysqli_v4.0.0
* *db-mysqli_v4.0.0*: Outdated class. See db-mysqli_v4.0.1
* *db-mysqli_v4.0.1*: My very own class to do queries to MySQL using the mysqli connector. It can do paramatrized
  queries and many other things.
* *debugInfo_v0.1*: Functions that aid me in the development of new functionality. Replacement of old functions.php.
* *extended-pgsql_v1.1.0*: The postgres implementation for db-mysqli_v3.1.1. Expect very soon a newer improved version!
  Based on PDO.
* *HTMLUtils_v1.0*: Outdated class. May or may not be updated in the future.
* *messageStack_v1.0.2*: _Very_ outdated class. May or may not be updated.
* *mysql-paginator_v1.1*: Another outdated class. Will not be updated anymore.
* *pid_v1.2*: Outdated class. See pid_v1.3.
* *pid_v1.3*: Class that can check making OS's calls whether we have a valid PID running or not. Works on Windows and
  Linux.
* *rut_v1.0*: Outdated class. See rutverifier_v1.1
* *rutverifier_v1.1*: This class will do all the checks you need in order to validate a chilean RUT or RUN. 

How to use these classes
--------------

I recommend the following steps: 
* Clone the project: <code>git clone git://github.com/unreal4u/u4u-classes.git u4u-classes</code>
* Include the following file: <code>include('u4u-classes/stable-versions.php');</code>
* Initialize the autoloader: <code>$u4uLoader = new u4u_autoloader();</code>
* Now anything you need to do is initializing the class you need: <code>$database = new db_mysqli();</code>
* Use the basename of the class you need to use, the autoloader will automatically load the last stable version of the
  class for you. 

Contact
--------------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
