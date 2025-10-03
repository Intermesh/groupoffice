#!/usr/bin/env bash
set -e
# group-office.com

# Check if PHP CLI is installed
hash php 2>/dev/null || { echo >&2 "The script requires php-cli but it's not installed. Run 'apt install php-cli' to install.  Aborting."; exit 1; }
hash curl 2>/dev/null || { echo >&2 "The script requires curl but it's not installed. Run 'apt install curl' to install.  Aborting."; exit 1; }

# Find the system architecture
DPKG_ARCH=$(dpkg --print-architecture)
if [[ "$DPKG_ARCH" = "i386" ]]; then
ARCH="x86_64"
elif [[ "$DPKG_ARCH" = "amd64" ]]; then
ARCH="x86_64"
elif [[ "$DPKG_ARCH" = "arm64" ]]; then
ARCH="aarch64"
fi

echo Detected architecture $ARCH;

URL=https://www.sourceguardian.com/loaders/download/loaders.linux-${ARCH}.tar.gz

echo "Downloading ${URL}"
# Download and extract
mkdir -p /usr/local/sourceguardian
curl "${URL}" | tar -xzf - -C /usr/local/sourceguardian
# Find PHP version
PHP_V=$(php -v)
PHP_VERSION=${PHP_V:4:3}

echo Detected PHP version $PHP_VERSION;

# Add the IonCube loader to the PHP configuration
echo "zend_extension=/usr/local/sourceguardian/ixed.${PHP_VERSION}.lin" \
    > "/etc/php/${PHP_VERSION}/mods-available/sourceguardian.ini"


echo "Enabling sourceguardian module"
# Make sure /usr/sbin is in the path or phpenmod will not work
export PATH=$PATH:/usr/sbin
phpenmod sourceguardian

echo "Reloading apache"

systemctl reload apache2

echo "Installed!"

php -v
