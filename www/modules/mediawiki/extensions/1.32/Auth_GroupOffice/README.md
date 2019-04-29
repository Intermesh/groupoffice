Version Requirements
============
GroupOffice: version 6.3.x
Mediawiki: version 1.32 or higher


Installation
============

Make sure mediawiki and GroupOffice are on the same domain. Because it uses the
GroupOffice cookie to read the logged in user.

Place this folder in the MediaWiki "extensions" folder

Add the following code at the bottom of your LocalSettings.php and set the correct url for GroupOffice:

```
wfLoadExtension( 'Auth_GroupOffice' );
$wgGoApiUrl = ''; // For example: 'http://example.group-office.com/' (Don't forget to include the trailing slash ( / ))
$wgEditPageFrameOptions = 'SAMEORIGIN';
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