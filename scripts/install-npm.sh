#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e

SASS="sass --no-source-map"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

cd "$DIR/www"
npm install
# npm ci --prefer-offline --audit=false --progress=false --fund=false

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
    npm run build;
    cd $DIR;

  done

  echo "DONE";
}

function buildAndInstallGOUIExceptCommunityAndBusiness() {
  echo BUILDING node modules inside "$1"...
  cd $DIR;
  find "$1" \
      \( -path '*/community/*' -o -path '*/business/*' -o -path '*/node_modules/*' \) -prune -o \
      -name package.json -print |
    while IFS= read -r line; do
    local NODE_DIR="$(dirname "${line}")";
    echo "BUILD:" $NODE_DIR;
    cd $NODE_DIR;
    npm ci;
    npm run build;
    cd $DIR;

  done

  echo "DONE";
}


echo "Building GOUI shared libs"
cd $DIR;
cd ./www/views/goui
npm run build

cd $DIR;
echo "DONE";

buildGOUI "./www/go/modules/community"
buildGOUI "./www/go/modules/business"
buildGOUI "./www/promodules"

# =)
buildAndInstallGOUIExceptCommunityAndBusiness "./www/go/modules"

echo $SECONDS;