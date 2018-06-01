#!/bin/bash

VERSION="2.3.8";
URL="http://download.z-push.org/final/";

#VERSION="2.3.4beta1";
#URL="http://download.z-push.org/beta/";

FOLDER=`echo $VERSION | cut -c 1-3`;

cd `dirname "$0"`

rm -f z-push-$VERSION.tar.gz
rm -Rf ../../z-push*

curl -O -L ${URL}/${FOLDER}/z-push-$VERSION.tar.gz

tar zxf z-push-$VERSION.tar.gz
mv z-push-$VERSION ../../z-push

cp -R backend/go ../../z-push/backend

cp config.php ../../z-push


./z-push-admin.php -a fixstates

rm -f z-push-$VERSION.tar.gz
echo "z-push_${VERSION} installed!"
