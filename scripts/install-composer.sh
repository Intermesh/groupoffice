#!/bin/bash
# This script installs the composer packages of core and all modules
#
# Usage: install-composer.sh [--no-dev] [--ignore-platform-reqs] [www dir]
#
#   --no-dev                Install for a release package: no dev packages and ignore platform requirements
#   --ignore-platform-reqs  Don't check the PHP version and extensions, for building on a host without them
#   www dir                 Directory to install in. Defaults to www of this checkout.

set -e

COMPOSER_ARGS="-n -o"
while [[ "$1" == --* ]]; do
  case "$1" in
    --no-dev) COMPOSER_ARGS="$COMPOSER_ARGS --no-dev --ignore-platform-reqs" ;;
    --ignore-platform-reqs) COMPOSER_ARGS="$COMPOSER_ARGS --ignore-platform-reqs" ;;
    *) echo "Unknown option: $1" >&2; exit 1 ;;
  esac
  shift
done

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

WWW="$( cd "${1:-$DIR/www}" && pwd )"
cd "$WWW"

echo "Installing PHP Composer packages"
export COMPOSER_ALLOW_SUPERUSER=1;
for line in $(find . -name composer.json -type f -not -path '*/vendor/*' -not -path '*/node_modules/*' | cut -c3- )
do
  COMPOSER_DIR="$(dirname "${line}")";

  if [ -z "$DOCKERCONTAINER" ]; then

    echo "Composer install:" $COMPOSER_DIR;
    cd $COMPOSER_DIR;
    composer install $COMPOSER_ARGS
    cd "$WWW"
  else
    echo "Composer install in docker: /usr/local/share/src/www/$COMPOSER_DIR"
    docker compose exec -w "/usr/local/share/src/www/$COMPOSER_DIR" $DOCKERCONTAINER composer install $COMPOSER_ARGS
  fi
done