#!/bin/bash
# This script builds SASS, installs composer packages and build's the GOUI modules

set -e
./install-composer.sh
./install-npm.sh