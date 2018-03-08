#!/bin/bash

PRG="$0"
OLDPWD=`pwd`
P=`dirname $PRG`
cd $P
if [ `pwd` != "/" ]
then
FULLPATH=`pwd`
else
FULLPATH=''
fi

VERSION=`cat ../www/go/base/Config.php | grep '$version' | sed -e 's/[^0-9\.]*//g'`

if [[ $VERSION =~ ^([0-9]\.[0-9])\.[0-9]{1,3}$ ]]; then
	MAJORVERSION=${BASH_REMATCH[1]}
fi

echo "Group-Office version: $VERSION"
echo "Major version: $MAJORVERSION"


cd /tmp

rm -Rf groupoffice-mailserver

mkdir groupoffice-mailserver

cd groupoffice-mailserver

svn export https://mschering@svn.code.sf.net/p/group-office/code/branches/groupoffice-$MAJORVERSION/debian-groupoffice-mailserver

mv debian-groupoffice-mailserver groupoffice-mailserver-$VERSION

#tar czf groupoffice-mailserver_$VERSION.orig.tar.gz groupoffice-mailserver-$VERSION

cd groupoffice-mailserver-$VERSION



debuild -rfakeroot
cd ..

mv *.deb /var/www/repos/html/poolsixtwo$1/main/

