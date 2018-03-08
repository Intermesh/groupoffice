
The minify.php script will compress all the javascript sources of Group-Office.
These compressed sources will be used when Group-Office is in production.
($config['debug']=false;).

Make sure you have java installed because the compressor requires it. You also
need the php command line interface

Usage on Linux:

php minify.php path/to/groupoffice

Example for Windows with WAMP installed:

C:\wamp\bin\php\php5.2.6\php.exe minify.php path\to\groupoffice
