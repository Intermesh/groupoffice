<?php
/*
 * 
 * Example virtual host file for servermanager server.
 * 
<VirtualHost *:80>

#DocumentRoot pointing to site module index.php
DocumentRoot /home/govhosts/example.group-office.eu/groupoffice/modules/site
 
ServerName example.com
ServerAlias www.example.com

ErrorLog /var/log/apache2/example.com.log

#Set explicit Group-Office config.php location
SetEnv GO_CONFIG /etc/groupoffice/example.group-office.eu/config.php

#/public alias for resources such as images and css files. The public folder is 
#located in $config['file_storage_path']
Alias /public /home/govhosts/example.group-office.eu/data/public

#Rewrite rules for site module
<Directory /home/govhosts/example.group-office.eu/groupoffice/modules/site>
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)\?*$ index.php/$1 [L,QSA]
</Directory>

</VirtualHost>
 
 * 
 *  
 * 
 * Alternatively create a document root for the site:
 * 
 * 1. Create a symlink or alias to $config['file_storage_path'].'public/' in
 *    the document root
 * 
 * 2. Copy this file to the document root * 
 * 
 * 
 * 3. Change $go = dirname(__FILE__).'/../../GO.php'; to point to the correct 
 *    location.
 * 
 * 4. Add this to a .htaccess file or to the VirtualHost file to enable pretty 
 *    URL's:
 * 
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)\?*$ index.php/$1 [L,QSA]
 * 
 * 
 


 */


//If the config.php file can't be found add this to the Apache configuration:
//SetEnv GO_CONFIG /etc/groupoffice/config.php

//Or you can use:
//define('GO_CONFIG_FILE', '/path/to/config.php');


$go = dirname(__FILE__).'/../../GO.php';
if(file_exists($go))
	require($go);
elseif(file_exists('/usr/share/groupoffice/GO.php'))
	require('/usr/share/groupoffice/GO.php');
else
	die("Please change the \$go variable to the correct location of GO.php");


//GO::setMaxExecutionTime(3, true);
//header("X-Frame-Options: SAMEORIGIN");
//header("Content-Security-Policy: default-src 'self' about:;font-src 'self' data:;script-src 'unsafe-eval' 'self' 'unsafe-inline';img-src 'self' about: data: http: https:;style-src 'self' 'unsafe-inline';frame-src 'self' https: http: groupoffice: groupoffices:;frame-ancestors 'self';");
//header("X-Content-Type-Options: nosniff");
//header("Strict-Transport-Security: max-age=31536000");
//header("X-XSS-Protection: 1;mode=block");

require(\GO::config()->root_path.'modules/site/components/Site.php');
\Site::launch();

