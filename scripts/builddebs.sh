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


svn export https://mschering@group-office.svn.sourceforge.net/svnroot/group-office/trunk/debian-groupoffice-mailserver

svn export https://mschering@group-office.svn.sourceforge.net/svnroot/group-office/trunk/debian-groupoffice

if [ $1 == "real" ]; then
	svn export https://mschering@group-office.svn.sourceforge.net/svnroot/group-office/trunk/www
	mv www debian-groupoffice/usr/share/groupoffice
fi


chmod 775 debian-groupoffice/DEBIAN/postinst
#chmod 775 debian-groupoffice-mailserver/DEBIAN/postinst
#chmod 775 debian-groupoffice-mailserver/DEBIAN/postrm

dpkg --build debian-groupoffice
#dpkg --build debian-groupoffice-mailserver

mv debian-groupoffice.deb $FULLPATH/groupoffice.deb
#mv debian-groupoffice-mailserver.deb $FULLPATH/groupoffice-mailserver.deb
