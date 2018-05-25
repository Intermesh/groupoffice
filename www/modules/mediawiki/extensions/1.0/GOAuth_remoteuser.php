<?php

/**
 * @see /Auth_remoteuser/Auth_remoteuser.php for info
 */

$wgMessagesDirs['Auth_remoteuser'] = __DIR__ . '/Auth_remoteuser/i18n';

$wgMinimalPasswordLength = 0;

$wgAuthRemoteuserAuthz = true;




$wgAuthRemoteuserNotify = false;

$wgAuthRemoteuserDomain = "";


$wgExtensionFunctions[] = function () {
	
	global $wgAuth;

	if ( $wgAuth instanceof GOAuth_remoteuser ) {

		$wgAuth->setupExtensionForRequest();
	} else {
		die( wfMessage( 'auth_remoteuser-wgautherror' ) );
	}
};

$wgAutoloadClasses['GOAuth_remoteuser'] = __DIR__ . '/GOAuth_remoteuser.body.php';
