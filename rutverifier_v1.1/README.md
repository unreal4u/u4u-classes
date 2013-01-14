rutverifier.class.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com).

About this class
--------

* Can be used to verify chilean RUT (Rol único tributario) or RUN (Rol único nacional). (<a href="http://www.registrocivil.cl/PortalOI/html/faq/Cod_Area_4/Cod_Tema_30/pregunta_155.html">Difference</a> [spanish])
* Will give you some information, such as the RUT/RUN being consulted is an enterprise (RUT) or a natural person (RUN).
* Allows you to make use of a blacklist (Useful for known frauders).
* Will also format the RUT/RUN into the correct format.
* Can deliver also a pure Javascript coded version to verify the validity of a RUT/RUN
    
Detailed description
---------

This package will do all kinds of things you can do with a RUT or RUN, such as:

Verifying that it is valid.
Finding out whether it is a RUT or a RUN.
Format it to the correct format to use

This package has been extensivily tested with xdebug, APC, PHPUnit testing and Suhosin so that no errors are present.

Basic usage
----------

<pre>include('rutverifier.class.php');
$rutVerifier = new rutverifier();
$result = $rutVerifier->isValidRUT('30.686.957-4');
</pre>
* Congratulations! Result does now contain true or false depending on the RUT/RUN being valid or not.
* **Please see examples for more options and advanced usage**

Pending
---------
* None

Version History
----------

* 1.0 : 
    * Initial version
    
* 1.1:
    * PHPUnit testing
    * Documentation improved (Created this README actually)
    * More examples
    * Solved some bugs

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
