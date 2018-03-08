<?php
require('www/GO.php');

$tpl = '{package} ({version}) sixtwo; urgency=low

  * Changes can be found in /usr/share/groupoffice/CHANGELOG.TXT

 -- Intermesh BV (Developer key) <info@intermesh.nl>  {date}';

//Mon, 26 May 2010 12:30:00 +0200
$date = date('D, j M Y H:i:s O');

$packages = array('groupoffice-com', 'groupoffice-mailserver', 'groupoffice-servermanager');

foreach($packages as $package){
	file_put_contents('debian-'.$package.'/debian/changelog', str_replace(
					array('{package}', '{version}', '{date}'),
					array($package, \GO::config()->version, $date),
					$tpl
					));
}
