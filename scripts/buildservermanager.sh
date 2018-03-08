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


cd /tmp

rm -Rf godebs

mkdir godebs

cd godebs


svn export https://mschering@group-office.svn.sourceforge.net/svnroot/group-office/trunk/debian-groupoffice-servermanager

chmod 775 debian-groupoffice-servermanager/DEBIAN/postinst
chmod 775 debian-groupoffice-servermanager/DEBIAN/postrm

dpkg --build debian-groupoffice-servermanager

mv debian-groupoffice-servermanager.deb $FULLPATH/groupoffice-servermanager_3.2.2_all.deb
