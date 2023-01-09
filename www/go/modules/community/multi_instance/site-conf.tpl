<VirtualHost *:443>

  # Each instance must have a dedicated WOPI subdomain for Microsoft:
  # https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/online/build-test-ship/environments#wopi-discovery-urls

  Define DOC_ROOT_{version} {docroot}
  ServerName {version}.wopi.{tld}
  ServerAlias {wopialiases}
  DocumentRoot ${DOC_ROOT_DEFAULT}

  #Include hostname for multi instance
  LogFormat "%V %h %l %u %t \"%r\" %s %b" vcommon

  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log vcommon

  <Directory ${DOC_ROOT_{version}}>
    Require all granted
    AllowOverride None
    Options FollowSymLinks
  </Directory>

  #For WOPI (O365 and LibreOffice online) support
  Alias /wopi ${DOC_ROOT_{version}}/go/modules/business/wopi/wopi.php

  SSLEngine on
  SSLCertificateKeyFile /etc/letsencrypt/live/wopi.{tld}/privkey.pem
  SSLCertificateFile /etc/letsencrypt/live/wopi.{tld}/fullchain.pem
</VirtualHost>

<VirtualHost *:443>
  Define DOC_ROOT_{version} {docroot}
  ServerName {servername}
  ServerAlias {aliases}
  DocumentRoot ${DOC_ROOT_{version}}

  #Include hostname for multi instance
  LogFormat "%V %h %l %u %t \"%r\" %s %b" vcommon

  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log vcommon

  <Directory ${DOC_ROOT_{version}}>
      Require all granted
      AllowOverride None
      Options FollowSymLinks
  </Directory>

  Alias /public ${DOC_ROOT_{version}}/public.php

  Alias /Microsoft-Server-ActiveSync ${DOC_ROOT_{version}}/modules/z-push/index.php

  #For CalDAV support
  Alias /caldav ${DOC_ROOT_{version}}/modules/caldav/calendar.php

  #For CardDAV support
  Alias /carddav ${DOC_ROOT_{version}}/modules/carddav/addressbook.php

  #For WebDAV support
  Alias /webdav ${DOC_ROOT_{version}}/modules/dav/files.php

  #For WOPI (O365 and LibreOffice online) support
  Alias /wopi ${DOC_ROOT_{version}}/go/modules/business/wopi/wopi.php

  #For OnlyOffice
  Alias /onlyoffice ${DOC_ROOT_{version}}/go/modules/business/onlyoffice/connector.php

  #DAV Service discovery. At least required for iOS7 support
  Redirect 301 /.well-known/carddav /carddav
  Redirect 301 /.well-known/caldav /caldav

  SSLEngine on 
  SSLCertificateKeyFile /etc/letsencrypt/live/{tld}/privkey.pem
  SSLCertificateFile /etc/letsencrypt/live/{tld}/fullchain.pem
</VirtualHost>


