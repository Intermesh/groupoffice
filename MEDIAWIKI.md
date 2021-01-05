# MediaWiki OpenId Integration

- Install and configure mediawiki
- Download extensions
  
        https://www.mediawiki.org/wiki/Extension:PluggableAuth
        https://www.mediawiki.org/wiki/Extension:OpenID_Connect

- Follow instructions on wiki ie maintenance/update.php + composer.local.php
- LocalSettings.php configuration

        wfLoadExtension( 'PluggableAuth' );
        wfLoadExtension( 'OpenIDConnect' );
        
        $wgGroupPermissions['*']['autocreateaccount'] = true;
        $wgGroupPermissions['*']['createaccount'] = true;
        
        $wgPluggableAuth_EnableLocalLogin = false;
        $wgPluggableAuth_EnableAutoLogin = true;
        
        $wgOpenIDConnect_Config['<<GROUPOFFICE_URL>>/api/oauth.php'] = [
            'clientID' => '<<CLIENT ID from GROUP OFFICE Oauth 2.0 module>>',
            'clientsecret' => '<<CLIENT SECRET from GROUP OFFICE Oauth 2.0 module>>',
            'scope' => array( 'openid')
        ];
