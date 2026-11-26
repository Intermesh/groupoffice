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

# pull the git clones of the GOUI npm packages, which www/node_modules/@intermesh may link to
for line in $DIR/links/*/;
do
  if [ -d "$line/.git" ]; then
    echo "Pulling $line"
    git -C "$line" pull
  fi
done

# pull main github repo
cd $DIR/www;

echo "Pulling main repository"

git pull
git submodule update --init

echo "All done"


