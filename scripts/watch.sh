#!/bin/bash

set -e
SASS="sass --watch"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

function buildGOUI() {
  echo BUILDING node modules inside "$1"...
  cd $DIR;
  for line in $(find $1 -name package.json -not -path '*/node_modules/*');
  do
    local NODE_DIR="$(dirname "${line}")";
    cd $NODE_DIR
    npm start &
    cd $DIR;

  done

  echo "DONE";
}

cd $DIR;
cd ./www/views/goui/
npm start &


echo "Building SASS"
cd $DIR;
for line in $(find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \) -not -path '*/goui/*' | sort -r );
do
  replace1=${line/src\/style.scss/style.css};
  replace2=${replace1/src\/style-mobile.scss/style-mobile.css};
  replace3=${replace2/src\/htmleditor.scss/htmleditor.css};
	$SASS $line $replace3 &
done



buildGOUI "./www/go/modules"


wait;