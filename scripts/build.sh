#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

# check if using docker. return code = 0 if docker compose is used, otherwise 1.
#docker compose config > /dev/null 2>&1
#DOCKERSTATUS=$?

set -e

DOCKERCONTAINER=$1

SASS="sass --no-source-map"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

cd "$DIR/www"


echo "Installing PHP Composer packages"
for line in $(find . -name composer.json -type f -not -path '*/vendor/*'  | cut -c3- )
do
  COMPOSER_DIR="$(dirname "${line}")";

  if [ -z "$DOCKERCONTAINER" ]; then

    echo "Composer install:" $COMPOSER_DIR;
    cd $COMPOSER_DIR;
    composer install -n -o
    cd $DIR/www
  else
    echo "Composer install in docker: /usr/local/share/src/www/$COMPOSER_DIR"
    docker compose exec -w "/usr/local/share/src/www/$COMPOSER_DIR" $DOCKERCONTAINER composer install -n -o
  fi
done

exit

echo "Building SASS\n"

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
    npm ci;
    npm run build;
    cd $DIR;

  done

  echo "DONE";
}

echo "Building GOUI shared libs..."
cd $DIR;
cd ./www/views/goui/goui
npm ci

cd ../groupoffice-core
npm ci

cd ..
npm ci
npm run build

cd $DIR;
echo "DONE";

buildGOUI "./www/go/modules"

cd www

for line in $(find . -name composer.json -type f -not -path '*/vendor/*')
do
  COMPOSER_DIR="$(dirname "${line}")";

  if [ -z "$DOCKERCONTAINER" ]; then
    echo "docker"
    docker compose exec $DOCKERCONTAINER -w "/usr/local/share/src/www/$COMPOSER_DIR" composer install -o
  else
    echo "Composer install:" $COMPOSER_DIR;
    cd $COMPOSER_DIR;
    composer install -o
    cd $DIR/www
  fi
done


