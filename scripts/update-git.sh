#!/bin/bash

# This script can be used to update an environment on a server.

set -e

CONFIG=$1



SASS="sass --no-source-map"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";


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
    git reset --hard
    git pull
    cd ..
  fi
done

# pull main github repo
cd ../../
git reset --hard
cd views/goui/goui
git reset --hard
cd ../groupoffice-core
git reset --hard
cd $DIR/../www;

echo "Pulling main repository"

git pull --recurse-submodules

for line in $(find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \) -not -path '*/goui/*' | sort -r );
do
  replace1=${line/src\/style.scss/style.css};
  replace2=${replace1/src\/style-mobile.scss/style-mobile.css};
  replace3=${replace2/src\/htmleditor.scss/htmleditor.css};
  echo $line - $replace3;
	$SASS $line $replace3;
done


function buildGOUI() {
  echo BUILDING node modules inside "$1"...
  cd $DIR;
  for line in $(find $1 -name package.json -not -path '*/node_modules/*');
  do
    local NODE_DIR="$(dirname "${line}")";
    echo "BUILD:" $NODE_DIR;
    cd $NODE_DIR;
    npm update;
    npm run build;
    cd $DIR;

  done

  echo "DONE";
}

echo "Building GOUI shared libs..."
cd $DIR;
cd ./www/views/goui/goui
npm update --include=dev
npm run build
cd ../groupoffice-core
npm update --include=dev
npm run build
cd ..
npm update --include=dev
npm run build
npm prune --production
cd $DIR;
echo "DONE";

buildGOUI "./www/go/modules"

cd www

composer update -n --no-dev -o

if [ -z "$CONFIG" ]; then
  echo NOTE: Not upgrading database because no config file was passed. eg. ./update-git.sh /etc/groupoffice/multi_instance/manage.group-office.com/config.php
  exit 1
else
  sudo -u www-data php cli.php core/System/upgrade -c=$CONFIG
fi


