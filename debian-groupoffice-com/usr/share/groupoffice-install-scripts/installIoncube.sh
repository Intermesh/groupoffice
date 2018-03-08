#!/usr/bin/env bash
 
# Check if PHP CLI is installed
hash php 2>/dev/null || { echo >&2 "The script requires php5-cli but it's not installed.  Aborting."; exit 1; }
 
# Find the system architecture
DPKG_ARCH=$(dpkg --print-architecture)
if [[ "$DPKG_ARCH" = "i386" ]]; then
  ARCH="x86"
elif [[ "$DPKG_ARCH" = "amd64" ]]; then
  ARCH="x86-64"
fi
 
# Download and extract
wget -q -O - "http://downloads2.ioncube.com/loader_downloads/ioncube_loaders_lin_${ARCH}.tar.gz" | tar -xzf - -C /usr/local
 
# Find PHP version
PHP_V=$(php -v)
PHP_VERSION=${PHP_V:4:3}
 
# Add the IonCube loader to the PHP configuration
echo "zend_extension=/usr/local/ioncube/ioncube_loader_lin_${PHP_VERSION}.so" \
    > '/etc/php5/apache2/conf.d/00-ioncube_loader.ini'

# Add the IonCube loader to the PHP CLI configuration
echo "zend_extension=/usr/local/ioncube/ioncube_loader_lin_${PHP_VERSION}.so" \
    > '/etc/php5/cli/conf.d/00-ioncube_loader.ini' 

# Restart services
for i in php5-fpm nginx apache2;do
    test -x /etc/init.d/$i && /etc/init.d/$i restart
done