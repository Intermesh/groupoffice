#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e

cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1

./install-composer.sh
./install-npm.sh
