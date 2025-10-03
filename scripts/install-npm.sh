#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e

SASS="sass --no-source-map"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

cd "$DIR/www"

echo "Building SASS"

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
    #rm -rf node_modules
    npm ci;
    npm run build;
    cd $DIR;

  done

  echo "DONE";
}

echo "Building GOUI shared libs"
cd $DIR;
cd ./www/views/goui/goui
#rm -rf node_modules
npm ci

cd ../groupoffice-core
#rm -rf node_modules
npm ci

cd ..
#rm -rf node_modules
npm ci
npm run build

cd $DIR;
echo "DONE";

buildGOUI "./www/go/modules"
buildGOUI "./www/promodules"
