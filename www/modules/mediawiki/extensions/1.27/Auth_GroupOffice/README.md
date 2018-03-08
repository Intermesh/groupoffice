Installation
============

Make sure mediawiki and GroupOffice are on the same domain. Because it uses the
GroupOffice cookie to read the logged in user.

Place this folder in the MediaWiki "extensions" folder

Add the following code at the bottom of your LocalSettings.php:

```
wfLoadExtension( 'Auth_GroupOffice' );
```

Make sure you set one of these 3 options too:

1.
```
// The extension can create accounts, because all anonymous users can.
$wgGroupPermissions['*']['createaccount'] = true;
```

2.
```
// If account creation by anonymous users is forbidden, then allow
// it to be created automatically (by the extension).
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['autocreateaccount'] = true;
```

3.
```
// Only login users automatically if known to the wiki already.
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['autocreateaccount'] = false;
```