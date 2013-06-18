cacheManager.class.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com)

About this class
--------

* Can be used to cache different things.
* Anything you choose, it will cache: strings, objects, boolean, etc.
* Change quickly from one type of cache to another (Included: file and APC).
* Posibility to write your own wrappers!
* With APC cache, you can delete a part of the cache, all of it or just one entry very easily.

Detailed description
---------

This package will use the cache engine of your choice to save things to cache.

There are many other very good engines out there (ZendCache / fluxBB-cache) that are probably better but this one is
made with speed, ease of use and stability in mind. It will also give you the capability to write your very own cache
mechanism and implement it very quickly as all calls are identical.
Besides, it was a nice exercise to learn something about reflection and some other stuff, but therefore **this class
will need PHP 5.3.0 as minimum**.

This package has been extensivily tested with xdebug, APC and Suhosin so that no errors are present.

Basic usage
----------

<pre>include('cacheManager.class.php');
try {
    $cache = new cacheManagerClass('apc');
} catch (cacheException $e) {
    print('Exception caught! Message: "'.$e->getMessage().'"');
} catch (versionException $e) {
    die($e->getMessage());
}
$secondExample = $cache->load('secondExample', array('en_US'));
if (empty($secondExample)) {
    // If empty, it means we don't have that cache yet or it is too old, create it
    $secondExample = 'Some very long process that would be useful to cache';
    // Save for 120 seconds
    $cache->save($secondExample, 'secondExample', null, 120);
}</pre>

* Congratulations! Everything is now properly saved in cache! Sit back and watch your server going nuts serving requests.
* Please see examples for more options and advanced usage

Pending
---------
* None

Version History
----------

* 1.1:
    * Tested on production
* 2.0:
    * It is now possible to make your own wrappers
    * Documentation improved (Created this README actually)
    * File-based cache
    * Exception enhancing
    * First release to public
* 2.1:
    * Bug fixes
    * Better separation
* 2.2:
    * Type hinting where possible
    * There is now a toggle debug mode function. When debug mode is enabled, it means that no calls are actually made to
      the cache functions, making this class rather useless, but very useful for debugging.
* 2.3:
    * Default base class which can be implementable if APC (or another of your choice) doesn't work. This way your code
      won't fail because at least the object and base methods will be defined

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
