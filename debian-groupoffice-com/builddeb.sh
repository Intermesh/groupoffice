#!/bin/bash

# For setting up gnupg agent:
# http://www.debian-administration.org/article/Gnu_Privacy_Guard_Agent_GPG/print

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

rm -Rf groupoffice-com
mkdir groupoffice-com
cd groupoffice-com

svn export https://mschering@svn.code.sf.net/p/group-office/code/branches/groupoffice-$MAJORVERSION/debian-groupoffice-com

echo Copying sources

cp -R /root/deploy/packages/groupoffice-com-$VERSION debian-groupoffice-com/usr/share/groupoffice
cp debian-groupoffice-com/usr/share/groupoffice/LICENSE.TXT debian-groupoffice-com

echo Renaming to groupoffice-com-$VERSION
mv debian-groupoffice-com groupoffice-com-$VERSION

echo Creating tar archive
tar --exclude=debian -czf groupoffice-com_$VERSION.orig.tar.gz groupoffice-com-$VERSION


cd groupoffice-com-$VERSION

debuild -rfakeroot
cd ..

mv *.deb /var/www/repos/html/poolsixtwo$1/main/
