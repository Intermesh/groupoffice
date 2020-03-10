#!/bin/bash
set -e

CONFIG=$1

if [ ! -z "$CONFIG" ]; then
  echo Please pass config file. eg. /etc/groupoffice/multi_instance/manage.group-office.com/config.php
  exit 1
fi

SASS=sassc
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

cd $DIR/../;

# pull promodules
cd  www/promodules
git pull

#pull all customer repos
cd ../go/modules

for line in $(ls -1 -d */);
do
  if [ "$line" != "community/" ]; then

    echo "Pulling $line"
    cd $line
    git pull
    cd ..
  fi
done

# pull main github repo
cd ../../
#git reset --hard
git pull

echo `pwd`

for line in $(find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \));
do
  replace1=${line/src\/style.scss/style.css};
  replace2=${replace1/src\/style-mobile.scss/style-mobile.css};
  replace3=${replace2/src\/htmleditor.scss/htmleditor.css};
  echo $line - $replace3;
	$SASS $line $replace3;
done

composer update --no-dev -o
sudo -u www-data php cli.php core/System/upgrade -c=/etc/groupoffice/multi_instance/manage.group-office.com/config.php

