#!/bin/bash

# This script can be used to update an environment on a server.

set -e

CONFIG=$1



SASS=sass --no-source-map
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

cd $DIR/../;

# pull promodules
echo "Pulling promodules"
cd  www/promodules
git pull

#pull all customer repos
cd ../go/modules

for line in $(ls -1 -d */);
do
  if [ -d "$line/.git" ]; then

    echo "Pulling $line"
    cd $line
    git pull
    cd ..
  fi
done

# pull main github repo
cd ../../
#git reset --hard

echo "Pulling main repository"

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

composer update -n --no-dev -o

if [ -z "$CONFIG" ]; then
  echo NOTE: Not upgrading database because no config file was passed. eg. ./update-git.sh /etc/groupoffice/multi_instance/manage.group-office.com/config.php
  exit 1
else
  sudo -u www-data php cli.php core/System/upgrade -c=$CONFIG
fi


