#!/bin/bash
# This script installs the NPM packages, which builds the GOUI modules with their "prepare" script, and builds SASS
#
# Usage: install-npm.sh [www dir]
#
#   www dir  Directory to build in. Defaults to www of this checkout.

set -e


SASS="npx sass --update --no-source-map --style=compressed"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

echo Installing NPM packages
cd "${1:-$DIR/www}"
#npm up
npm ci --prefer-offline --audit=false --progress=false --fund=false

echo "Building Extjs3 SASS"
for line in $(find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \) -not -path '*/goui/*' | sort -r );
do
  replace1=${line/src\/style.scss/style.css};
  replace2=${replace1/src\/style-mobile.scss/style-mobile.css};
  replace3=${replace2/src\/htmleditor.scss/htmleditor.css};
  echo $line - $replace3;
	$SASS $line $replace3;
done

echo "Done"