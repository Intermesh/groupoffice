#!/bin/bash

#dependencies:
#sudo apt-get install gnupg-agent pinentry-curses pbuilder php5-cli php5-curl

eval "$(gpg-agent --daemon)"

svn up
php ./createchangelogs.php
svn commit -m 'Updated changelogs'

#send to repos
./debian-groupoffice-servermanager/builddeb.sh $1
./debian-groupoffice-mailserver/builddeb.sh $1
./debian-groupoffice-com/builddeb.sh $1
