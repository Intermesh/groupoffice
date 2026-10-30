#!/bin/bash

# Downloads multiple versions for upgrading on CLI
#
# make sure:
# - all license files are installed up to v6.4
# - Remove older version from this script that are not relevant
# - Set LICENSE
# - Set the correct PHP7 and PHP8 versions (Install older using Sury repo (apt install php7.4-cli php7.4-mbstring php7.4-mysql php7.4-xml php7.4-curl php7.4-gd php7.4-intl php7.4-xsl))
# - Set config file location
#
set -e

CONFIG=/etc/groupoffice/config.php
LICENSE=""
PHP7=/usr/bin/php7.4
PHP8=/usr/bin/php

wget https://sourceforge.net/projects/group-office/files/6.3/groupoffice-6.3.99-php-71.tar.gz/download
tar zxf download
rm download

wget https://sourceforge.net/projects/group-office/files/6.4/groupoffice-6.4.247-php-71.tar.gz/download
tar zxf download
rm download

wget https://github.com/Intermesh/groupoffice/releases/download/v6.5.113/groupoffice-6.5.113.tar.gz
tar zxf groupoffice-6.5.113.tar.gz

wget https://github.com/Intermesh/groupoffice/releases/download/v6.6.177/groupoffice-6.6.177.tar.gz
tar zxf groupoffice-6.6.177.tar.gz

wget https://github.com/Intermesh/groupoffice/releases/download/v6.7.54/groupoffice-6.7.54.tar.gz
tar zxf groupoffice-6.7.54.tar.gz

wget https://github.com/Intermesh/groupoffice/releases/download/v6.8.172/groupoffice-6.8.172.tar.gz
tar zxf groupoffice-6.8.172.tar.gz

wget https://github.com/Intermesh/groupoffice/releases/download/v25.0.108/groupoffice-25.0.108.tar.gz
tar zxf groupoffice-25.0.108.tar.gz

wget https://github.com/Intermesh/groupoffice/releases/download/v26.0.55/groupoffice-26.0.55.tar.gz
tar zxf groupoffice-26.0.55.tar.gz


printf "\n\n --> UPGRADING TO 6.3\n\n"

sudo -u www-data $PHP7 ./groupoffice-6.3.99-php-71/install/upgrade.php -c=$CONFIG

printf "\n\n --> UPGRADING TO 6.4\n\n"
sudo -u www-data $PHP7 ./groupoffice-6.4.247-php-71/cli.php core/System/upgrade -c=$CONFIG

printf "\n\n --> UPGRADING TO 6.5\n\n"
sudo -u www-data $PHP7 ./groupoffice-6.5.113/cli.php core/System/setLicense --key=$LICENSE
sudo -u www-data $PHP7 ./groupoffice-6.5.113/cli.php core/System/upgrade -c=$CONFIG

printf "\n\n --> UPGRADING TO 6.6\n\n"
sudo -u www-data $PHP7 ./groupoffice-6.6.177/cli.php core/System/upgrade -c=$CONFIG


printf "\n\n --> UPGRADING TO 6.7\n\n"
sudo -u www-data $PHP7 ./groupoffice-6.7.54/cli.php core/System/upgrade -c=$CONFIG

#Switch to php8.2+ here
printf "\n\n --> UPGRADING TO 6.8\n\n"
sudo -u www-data $PHP8 ./groupoffice-6.8.172/cli.php core/System/upgrade -c=$CONFIG

printf "\n\n --> UPGRADING TO 25.0\n\n"
sudo -u www-data $PHP8 ./groupoffice-25.0.108/cli.php core/System/upgrade -c=$CONFIG

printf "\n\n --> UPGRADING TO 26.0\n\n"
sudo -u www-data $PHP8 ./groupoffice-26.0.55/cli.php core/System/upgrade -c=$CONFIG