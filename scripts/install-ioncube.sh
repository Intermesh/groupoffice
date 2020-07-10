#!/usr/bin/env bash
# group-office.com

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
    > "/etc/php/${PHP_VERSION}/mods-available/ioncube.ini"

phpenmod ioncube

mv "/etc/php/${PHP_VERSION}/apache2/conf.d/20-ioncube.ini" "/etc/php/${PHP_VERSION}/apache2/conf.d/00-ioncube.ini"
mv "/etc/php/${PHP_VERSION}/cli/conf.d/20-ioncube.ini" "/etc/php/${PHP_VERSION}/cli/conf.d/00-ioncube.ini"

echo "Reloading apache"

systemctl reload apache2

echo "Installed!"

php -v
