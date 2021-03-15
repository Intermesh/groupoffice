#!/bin/bash

# This script can be used to update an environment on a server.

set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

cd $DIR/../;

# pull promodules
cd  www/promodules
git pull
git merge $1

#pull all customer repos
cd ../go/modules/business
git pull
git merge $1

cd $DIR/../;
git pull
git merge $1