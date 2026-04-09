#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e


SASS="npx sass --update --no-source-map --style=compressed"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

echo Installing NPM packages
cd "$DIR/www"
npm ci --prefer-offline --audit=false --progress=false --fund=false

echo "Building SASS"
for line in $(find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \) -not -path '*/goui/*' | sort -r );
do
  replace1=${line/src\/style.scss/style.css};
  replace2=${replace1/src\/style-mobile.scss/style-mobile.css};
  replace3=${replace2/src\/htmleditor.scss/htmleditor.css};
  echo $line - $replace3;
	$SASS $line $replace3;
done

echo "Done"