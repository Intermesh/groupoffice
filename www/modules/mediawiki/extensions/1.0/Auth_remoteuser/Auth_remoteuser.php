<?php
// vim:sw=2:softtabstop=2:textwidth=80
//
// This program is free software: you can redistribute it and/or
// modify it under the terms of the GNU General Public License as
// published by the Free Software Foundation, either version 2 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see
// <http://www.gnu.org/licenses/>.
//
// Copyright 2006 Otheus Shelling
// Copyright 2007 Rusty Burchfield
// Copyright 2009 James Kinsman
// Copyright 2010 Daniel Thomas
// Copyright 2010 Ian Ward Comfort
// Copyright 2014 Mark A. Hershberger
//
// In 2009, the copyright holders determined that the original
// publishing of this code under GPLv3 was legally and logistically in
// error, and re-licensed it under GPLv2.
//
// See http://www.mediawiki.org/wiki/Extension:Auth_remoteuser
//
// * Compatiblity with version 1.9 of MediaWiki. -- Rusty Burchfield
// * Optional settings. -- Emmanuel Dreyfus
// * Compatiblity with version 1.16 of MediaWiki. -- VibroAxe (James
//   Kinsman)
// * Allow domain substitution for Integrated Windows Authentication.
//   -- VibroAxe (James Kinsman)
// * Add optional $wgAuthRemoteuserMailDomain and remove hardcoding of
//   permissions for anonymous users. -- drt24 (Daniel Thomas)
// * Detect mismatches between the session user and REMOTE_USER.
//   -- Ian Ward Comfort
// * Refactored to allow easier extensibility. -- Mark A. Hershberger.
// * Updated to match extension name in repo and newer methods on
//   AuthPlugin. -- Mark A. Hershberger
//
// Add these lines to your LocalSettings.php
//
// // Don't let anonymous people do things...
// $wgGroupPermissions['*']['createaccount']   = false;
// $wgGroupPermissions['*']['read']            = false;
// $wgGroupPermissions['*']['edit']            = false;
//
// /* This is required for Auth_remoteuser operation
// require_once('extensions/Auth_remoteuser.php');
// $wgAuth = new Auth_remoteuser();
//
// The constructor of Auth_remoteuser registers a hook to do the
// automatic login.  Storing the Auth_remoteuser object in $wgAuth
// tells mediawiki to use that object as the AuthPlugin.  This way the
// login attempts by the hook will be handled by us.
//
// You probably want to edit the initUser function to set the users
// real name and email address properly for your configuration.

// Extension credits that show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Auth_remoteuser',
	'version' => '1.1.4',
	'author' => array( 'Otheus Shelling', 'Rusty Burchfield',
		'James Kinsman', 'Daniel Thomas', 'Ian Ward Comfort',
		'[[mw:User:MarkAHershberger|Mark A. Hershberger]]'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Auth_remoteuser',
	'descriptionmsg' => 'auth_remoteuser-desc',
);

$wgMessagesDirs['Auth_remoteuser'] = __DIR__ . '/i18n';

// We must allow zero length passwords. This extension does not work
// in MW 1.16 without this.
$wgMinimalPasswordLength = 0;

$wgAuthRemoteuserAuthz = true;

/* User's name */
$wgAuthRemoteuserName = isset( $_SERVER["AUTHENTICATE_CN"] )
	? $_SERVER["AUTHENTICATE_CN"]
	: '';

/* User's Mail */
$wgAuthRemoteuserMail = isset( $_SERVER["AUTHENTICATE_MAIL"] )
	? $_SERVER["AUTHENTICATE_MAIL"]
	: '';

/* Do not send mail notifications */
$wgAuthRemoteuserNotify = false;

/* Remove DOMAIN\ from the beginning or @DOMAIN at the end of an IWA
 * username.  Set to your own netbios domain if you need this done. */
$wgAuthRemoteuserDomain = "";

/* User's mail domain to append to the user name to make their email
 * address */
$wgAuthRemoteuserMailDomain = "example.com";

/**
 * This hook is registered by the Auth_remoteuser constructor.  It
 * will be called on every page load.  It serves the function of
 * automatically logging in the user.  The Auth_remoteuser class is an
 * AuthPlugin and handles the actual authentication, user creation,
 * etc.
 *
 * Details:
 * 1. Check to see if the user has a session and is not anonymous.  If
 *    this is true, check whether REMOTE_USER matches the session
 *    user.  If so, we can just return; otherwise we must logout the
 *    session user and login as the REMOTE_USER.
 * 2. If the user doesn't have a session, we create a login form with
 *    our own fake request and ask the form to authenticate the user.
 *    If the user does not exist authenticateUserData will attempt to
 *    create one.  The login form uses our Auth_remoteuser class as an
 *    AuthPlugin.
 *
 * Note: If cookies are disabled, an infinite loop /might/ occur?
 */
$wgExtensionFunctions[] = function () {
	global $wgAuth;

	if ( $wgAuth instanceof Auth_remoteuser ) {
		$wgAuth->setupExtensionForRequest();
	} else {
		die( wfMessage( 'auth_remoteuser-wgautherror' ) );
	}
};

$wgAutoloadClasses['Auth_remoteuser'] = __DIR__ . '/Auth_remoteuser.body.php';
