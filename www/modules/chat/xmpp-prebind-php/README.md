XMPP Prebind for PHP
====================

This class is for [prebinding](http://metajack.im/2009/12/14/fastest-xmpp-sessions-with-http-prebinding/) a XMPP Session with PHP.

Usage
=====
* Clone the repo
* In your file where you want to do the prebinding:

```php
/**
 * Comment here for explanation of the options.
 *
 * Create a new XMPP Object with the required params
 *
 * @param string $jabberHost Jabber Server Host
 * @param string $boshUri    Full URI to the http-bind
 * @param string $resource   Resource identifier
 * @param bool   $useSsl     Use SSL (not working yet, TODO)
 * @param bool   $debug      Enable debug
 */
$xmppPrebind = new XmppPrebind('your-jabber-host.tld', 'http://your-jabber-host/http-bind/', 'Your XMPP Clients resource name', false, false);
$xmppPrebind->connect($username, $password);
$xmppPrebind->auth();
$sessionInfo = $xmppPrebind->getSessionInfo(); // array containing sid, rid and jid
```

* If you use [Candy](http://amiadogroup.github.com/candy), change the `Candy.Core.Connect()` line to the following:

```javascript
Candy.Core.attach('<?php echo $sessionInfo['jid'] ?>', '<?php echo $sessionInfo['sid'] ?>', '<?php echo $sessionInfo['rid'] ?>');
```

* You should now have a working prebinding with PHP

Debugging
=========
If something doesn't work, you can enable Debug. Debug output is logged to [FirePHP](http://www.firephp.org/), so you have to install that first.

Other Languages
===============
There exist other projects for other languages to support a prebind. Go googling :)

Be aware
========
This class is in no way feature complete. There may also be bugs. I'd appreciate it if you contribute or submit bug reports.

Thanks.