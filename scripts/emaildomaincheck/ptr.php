<?php

exec("postconf -h myhostname", $output, $ret);

if($ret !== 0) {
	exit("Could not get myhostname");
}

$myhostname = $output[0];

echo "myhostname = " .$myhostname ."\n";

$nslookup = exec('nslookup '.escapeshellarg($myhostname), $output, $ret);

/**
 * root@smtp:~# nslookup smtp.group-office.com
 * Server:    127.0.0.1
 * Address:  127.0.0.1#53
 *
 * Non-authoritative answer:
 * Name:  smtp.group-office.com
 * Address: 149.210.243.200
 * Name:  smtp.group-office.com
 * Address: 2a01:7c8:aabb:223:5054:ff:fe6a:2481
 */

$regex = '/^Name:\s+'.preg_quote($myhostname)."\s+Address:\s+([^\s]+)$/m";

preg_match_all($regex, implode("\n",$output), $matches);

$ips = $matches[1];

foreach($ips as $ip) {

	echo "myIP = " .$ip ."\n";

	$resolved = gethostbyaddr($ip);

	echo "resolved = " . $resolved . " ";

	echo ($resolved == $myhostname) ? "SUCCESS" : "FAILED";

	echo "\n";

}