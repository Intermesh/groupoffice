<VirtualHost *:443>
Define DOC_ROOT_{version} {docroot}
ServerName {servername}
ServerAlias {aliases}
DocumentRoot ${DOC_ROOT_{version}}

#Include hostname for multi instance
LogFormat "%V %h %l %u %t \"%r\" %s %b" vcommon

ErrorLogFormat "[%V] [%t] [%l] [pid %P] %F: %E: [client %a] %M"
ErrorLog ${APACHE_LOG_DIR}/groupoffice_error.log
CustomLog ${APACHE_LOG_DIR}/groupoffice_access.log vcommon

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

#openID service discovery
Alias /.well-known/openid-configuration ${DOC_ROOT_{version}}/api/oauth.php/.well-known/openid-configuration

#autoconfig
Alias /mail/config-v1.1.xml ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autoconfig.php
Alias /v1.1/mail/config-v1.1.xml ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autoconfig.php
Alias /.well-known/autoconfig/mail/config-v1.1.xml ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autoconfig.php

#autodiscover
Alias /autodiscover/autodiscover.json ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autodiscover-json.php
Alias /Autodiscover/Autodiscover.xml ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autodiscover.php
Alias /autodiscover/autodiscover.xml ${DOC_ROOT_{version}}/go/modules/community/autoconfig/autodiscover.php

SSLEngine on
SSLCertificateKeyFile /etc/letsencrypt/live/{tld}/privkey.pem
SSLCertificateFile /etc/letsencrypt/live/{tld}/fullchain.pem

# Optionally enable php fpm to run different PHP version
## Increased timeout for long running requests (sse, activesync)
#ProxyTimeout 86400
#
##Pass authorization header
#SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
#<FilesMatch \.php$>
#    SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
#</FilesMatch>

</VirtualHost>


