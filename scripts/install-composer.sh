#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

cd "$DIR/www"

echo "Installing PHP Composer packages"
export COMPOSER_ALLOW_SUPERUSER=1;
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