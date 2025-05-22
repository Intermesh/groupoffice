#!/bin/bash
# This script updates all git repo's
set -e

CONFIG=$1
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $DIR/../;
DIR="$(pwd)";

# pull promodules
if [ -d "www/promodules" ]; then
  echo "Pulling promodules"
  cd  www/promodules
  git pull
  cd ../../
fi

#pull all customer repos
cd www/go/modules

for line in $(ls -1 -d */);
do
  if [ -d "$line/.git" ]; then

    echo "Pulling $line"
    cd $line
   #git reset --hard
    git pull
    cd ..
  fi
done

# pull main github repo
cd ../../
#git reset --hard
cd views/goui/goui
#git reset --hard
cd ../groupoffice-core
#git reset --hard
cd $DIR/www;

echo "Pulling main repository"

git pull
git submodule update --init



