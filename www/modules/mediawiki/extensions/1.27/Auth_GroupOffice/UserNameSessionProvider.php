<?php

namespace MediaWiki\Extensions\Auth_GroupOffice;

use Closure;
use Hooks;
use MediaWiki\Session\CookieSessionProvider;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use Sanitizer;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Title;
use User;
use WebRequest;
/**
 * This file is part of the MediaWiki extension Auth_remoteuser.
 *
 * Copyright (C) 2017 Stefan Engelhardt and others (for a complete list of
 *                    authors see the file `extension.json`)
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program (see the file `COPYING`); if not, write to the
 *
 *   Free Software Foundation, Inc.,
 *   59 Temple Place, Suite 330,
 *   Boston, MA 02111-1307
 *   USA
 *
 * @file
 */

/**
 * MediaWiki session provider for arbitrary user name sources.
 *
 * A `UserNameSessionProvider` uses a given user name (the remote source takes
 * total responsibility in authenticating that user) and tries to tie it to an
 * according local MediaWiki user.
 *
 * This provider acts the same as the `CookieSessionProvider` but in contrast
 * does not allow anonymous users per session as the `CookieSessionProvider`
 * does. In this case a user will be set which is identified by the given
 * user name. Additionally this provider will create a new session with the
 * given user if no session exists for the current request. The default
 * `CookieSessionProvider` creates new sessions on specific user activities
 * only.
 *
 * @see CookieSessionProvider::provideSessionInfo()
 * @version 2.0.0
 * @since 2.0.0
 */
class UserNameSessionProvider extends CookieSessionProvider {

	/**
	 * The hook identifier this class provides to filter user names.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	const HOOKNAME = "UserNameSessionProviderFilterUserName";

	/**
	 * The remote user name(s) given as an array.
	 *
	 * @var string[]
	 * @since 2.0.0
	 */
	protected $remoteUserNames;

	/**
	 * User preferences applied in the moment of local account creation only.
	 *
	 * The preferences are given as an array of key value pairs. Each key relates
	 * to an according user preference option of the same name. Closures as values
	 * getting evaluated and should return the according value. The following keys
	 * are handled individually:
	 * * `realname` - Specifies the users real (display) name.
	 * * `email` - Specifies the users email address.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $userPrefs;

	/**
	 * User preferences applied to the user object on each request.
	 *
	 * @see self::$userPrefs
	 * @var array
	 * @since 2.0.0
	 */
	protected $userPrefsForced;

	/**
	 * Urls in links which differ from the default ones. The following keys are
	 * supported in this associative array:
	 * * 'logout' - Redirect to this url on logout.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $userUrls;

	/**
	 * Indicates if the automatically logged-in user can switch to another local
	 * MediaWiki account while still beeing identified by the remote user name.
	 *
	 * @var boolean
	 * @since 2.0.0
	 */
	protected $switchUser;

	/**
	 * Indicates if special pages related to authentication getting removed by us.
	 *
	 * @var boolean
	 * @since 2.0.0
	 */
	protected $removeAuthPagesAndLinks;

	/**
	 * A token unique to the remote source session.
	 *
	 * This must persist requests and if it changes, it indicates a change in the
	 * remote session.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $remoteToken;

	/**
	 * The constructor processes the class configuration.
	 *
	 * In addition to the keys of the parents constructor parameter `params` this
	 * constructor evaluates the following keys:
	 * * `remoteUserNames` - Either a string/closure or an array of
	 *   strings/closures listing the given remote user name(s). Each closure
	 *   should return a string.
	 * * `userPrefs` - @see self::$userPrefs
	 * * `userPrefsForced` - @see self::$userPrefsForced
	 * * `userUrls` - @see self::$userUrls
	 * * `switchUser` - @see self::$switchUser
	 * * `removeAuthPagesAndLinks` - @see self::$removeAuthPagesAndLinks
	 *
	 * @since 2.0.0
	 */
	public function __construct( $params = [] ) {

		# Setup configuration defaults.
		$defaults = [
			'remoteUserNames' => [],
			'userPrefs' => null,
			'userPrefsForced' => null,
			'userUrls' => null,
			'switchUser' => false,
			'removeAuthPagesAndLinks' => true
		];

		# Sanitize configuration and apply to own members.
		foreach( $defaults as $key => $default) {
			$value = $default;
			if ( array_key_exists( $key, $params ) ) {
				switch( $key ) {
					case 'remoteUserNames':
						$final = [];
						$names = $params[ $key ];
						if ( !is_array( $names ) ) {
							$names = [ $names ];
						}
						foreach( $names as $name ) {
							if ( is_string( $name ) || $name instanceof Closure ) {
								$final[] = $name;
							}
						}
						if ( count( $final ) ) {
							$value = $final;
						}
						break;
					case 'userPrefs':
					case 'userPrefsForced':
					case 'userUrls':
						if ( is_array( $params[ $key ] ) ) {
							$value = $params[ $key ];
						}
						break;
					default:
						$value = (bool)$params[ $key ];
						break;
				}
			}
			$this->{ $key } = $value;
		}

		# The cookie prefix used by our parent will be the same as our class name to
		# not interfere with cookies set by other instances of our parent.
		$prefix = str_replace( '\\', '_', get_class( $this ) );
		$params += [
			'sessionName' => $prefix . '_session',
			'cookieOptions' => []
		];
		$params[ 'cookieOptions' ] += [ "prefix" => $prefix ];

		# Let our parent sanitize the rest of the configuration.
		parent::__construct( $params );
	}

	/**
	 * Method get's called by the SessionManager on every request for each
	 * SessionProvider installed to determine if this SessionProvider has
	 * identified a possible session.
	 *
	 * @since 2.0.0
	 */
	public function provideSessionInfo( WebRequest $request ) {

		# Loop through user names given by all remote sources. First hit, which
		# matches a usable local user name, will be used for our SessionInfo then.
		foreach ( $this->remoteUserNames as $remoteUserName ) {

			if ( $remoteUserName instanceof Closure ) {
				$remoteUserName = call_user_func( $remoteUserName );
			}

			# Keep info about remote-to-local user name processing for logging and
			# and later for provider metadata.
			$metadata = [ 'remoteUserName' => (string)$remoteUserName ];

			if ( !is_string( $remoteUserName ) || empty( $remoteUserName ) ) {
				$this->logger->warning(
					"Can't login remote user '{remoteUserName}' automatically. " .
					"Given remote user name is not of type string or empty.",
					$metadata
				);
				continue;
			}

			# Process each given remote user name if needed, e.g. strip NTLM domain,
			# replace characters, rewrite to another username or even blacklist it by
			# returning false. This can be used by the wiki administrator to adjust
			# this SessionProvider to his specific needs.
			$filteredUserName = $remoteUserName;
			if ( !Hooks::run( static::HOOKNAME, [ &$filteredUserName ] ) ) {
				$metadata[ 'filteredUserName' ] = $filteredUserName;
				$this->logger->warning(
					"Can't login remote user '{remoteUserName}' automatically. " .
					"Blocked this user when applying filter to '{filteredUserName}'.",
					$metadata
				);
				continue;
			}
			$metadata[ 'filteredUserName' ] = (string)$filteredUserName;

			if ( !is_string( $filteredUserName ) || empty( $filteredUserName ) ) {
				$this->logger->warning(
					"Can't login remote user '{remoteUserName}' automatically. " .
					"Filtered remote user name '{filteredUserName}' is not of " .
					"type string or empty.",
					$metadata
				);
				continue;
			}

			# Create a UserInfo (and User) object by given user name. The factory
			# method will take care of correct validation of user names. It will also
			# canonicalize it (e.g. transform the first letter to uppercase).
			#
			# An exception gets thrown when the given user name is not 'usable' as an
			# user name for the wiki, either blacklisted or contains invalid characters
			# or is an ip address.
			#
			# @see User::getCanonicalName()
			# @see User::isUsableName()
			# @see Title::newFromText()
			try {
				$userInfo = UserInfo::newFromName( $filteredUserName, true );
			} catch ( InvalidArgumentException $e ) {
				$metadata[ 'exception' ] = $e;
				$this->logger->warning(
					"Can't login remote user '{remoteUserName}' automatically. " .
					"Filtered name '{filteredUserName}' is not usable as an " .
					"user name in MediaWiki.: {exception}",
					$metadata
				);
				continue;
			}
			$metadata[ 'userId' ] = $userInfo->getId();
			$metadata[ 'canonicalUserName' ] = $userInfo->getName();

			# Let our parent class find a valid SessionInfo.
			$sessionInfo = parent::provideSessionInfo( $request );

			# Our parent class provided a session info, but the `$wgGroupPermission` for
			# creating user accounts was changed while using this extension. This leds
			# to a behaviour where a new user won't be created automatically even if the
			# wiki administrator enabled (auto) account creation by setting the specific
			# group permission `createaccount` or `autocreateaccount` to `true`.
			#
			# This happens in rare circurmstances only: The global permission forbids
			# account creation. A new user requests the wiki and a specific session is
			# getting created (by our parent). Then the `AuthManager` permits further
			# account creation attempts due to global permission by marking this session
			# as blacklistet. This blacklist info is stored inside the session itself.
			# Now when the wiki admin changes the global account creation permission to
			# `true` and the new user wants to access the wiki, then his account is not
			# created automatically due to this blacklist marking inside his current
			# session, identified by our parent class. Hence we throw this session away
			# in these rare cases (We could manipulate the session itself and delete that
			# `AuthManager::AutoCreateBlacklist` key instead, but that would look like
			# real hacked code inside this method).
			#
			# @see AuthManager::AutoCreateBlacklist
			# @see AuthManager::autoCreateUser()
			if ( $sessionInfo && !$userInfo->getId() ) {
				$anon = $userInfo->getUser();
				$permissions = $anon->getGroupPermissions( $anon->getEffectiveGroups() );
				if ( in_array( 'autocreateaccount', $permissions, true ) ||
					in_array( 'createaccount', $permissions, true ) ) {
					$this->logger->warning(
						"Renew session due to global permission change " .
						"in (auto) creating new users."
					);
					$sessionInfo = null;
				}
			}

			# If our parent provides a session info, it could be from an old request
			# where the old remote user name doesn't match the one used for the current
			# request. This happens when the client switched the user remotely (because
			# he has access to two different accounts at the remote source). Therefore
			# we have to mark the local authentication part (the cookie, because we
			# inherit from `CookieSessionProvider`) and compare it against the current
			# request. We use the `filteredUserName` instead of `remoteuserName` for the
			# comparison, because the wiki admin could have mapped two differing remote
			# user names to the same local username with an according filter. For the
			# marking itself we have to overwrite some inherited methods.
			#
			# @see self::getCookieDataToExport()
			# @see self::persistSession()
			if ( $sessionInfo ) {
				$prefix = $this->cookieOptions[ 'prefix' ];
				$old = $this->getCookie( $request, 'RemoteToken', $prefix );
				if ( $old !== $filteredUserName ) {
					$this->logger->warning(
						"Renew local session due to remote session change."
					);
					$sessionInfo = null;
				}
			}

			# Our parent class couldn't provide any info. This means we can create a
			# new session with our identified user.
			if ( !$sessionInfo ) {
				$sessionInfo = new SessionInfo( $this->priority, [
					'provider' => $this,
					'id' => $this->manager->generateSessionId(),
					'userInfo' => $userInfo
					]
				);
			}

			# The current session identifies an anonymous user, therefore we have to
			# use the forceUse flag to set our identified user. If we are configured
			# to forbid user switching, force the usage of our identified user too.
			if ( !$sessionInfo->getUserInfo() || !$sessionInfo->getUserInfo()->getId()
				|| ( !$this->switchUser && $sessionInfo->getUserInfo()->getId() !== $userInfo->getId() ) ) {
				$sessionInfo = new SessionInfo( $sessionInfo->getPriority(), [
					'copyFrom' => $sessionInfo,
					'userInfo' => $userInfo,
					'forceUse' => true
					]
				);
			}

			# Store info about user in the provider metadata.
			$metadata[ 'canonicalUserNameUsed' ] = $sessionInfo->getUserInfo()->getName();
			$sessionInfo = new SessionInfo( $sessionInfo->getPriority(), [
				'copyFrom' => $sessionInfo,
				'metadata' => $metadata
				]
			);

			return $sessionInfo;
		}

		# We didn't identified anything, so let other SessionProviders do their work.
		return null;
	}

	/**
	 * Never use the stored metadata and return the provided one in any case.
	 *
	 * But let our parents implementation of this method decide on his own for the
	 * other members.
	 *
	 * @since 2.0.0
	 */
	public function mergeMetadata( array $savedMetadata, array $providedMetadata ) {
		$keys = [
			'userId',
			'remoteUserName',
			'filteredUserName',
			'canonicalUserName',
			'canonicalUserNameUsed'
		];
		foreach ( $keys as $key ) {
			$savedMetadata[ $key ] = $providedMetadata[ $key ];
		}
		return parent::mergeMetadata( $savedMetadata, $providedMetadata );
	}

	/**
	 * The SessionManager selected us as the SessionProvider for this request.
	 *
	 * Now we can add additional information to the requests user object and
	 * remove some special pages and personal urls from the clients frontend.
	 *
	 * @since 2.0.0
	 */
	public function refreshSessionInfo( SessionInfo $info, WebRequest $request, &$metadata ) {

		$this->logger->info( "Setting up auto login session for remote user name " .
			"'{remoteUserName}' (mapped to MediaWiki user '{canonicalUserName}', " .
			"currently active as MediaWiki user '{canonicalUserNameUsed}').",
			$metadata
		);

		$disableSpecialPages = [];
		$disablePersonalUrls = [];
		$preferences = ( $this->userPrefsForced ) ? $this->userPrefsForced : [];

		# Disable any special pages related to user switching.
		if ( !$this->switchUser ) {
			$disableSpecialPages += [
				'Userlogin',
				'Userlogout',
				'CreateAccount',
				'LinkAccounts',
				'UnlinkAccounts',
				'ChangeCredentials',
				'RemoveCredentials'
			];
		}

		# This can only be true, if our `switchUser` member is set to true and the
		# user identified by us uses another local wiki user for this session.
		$switchedUser = ( $info->getUserInfo()->getId() !== $metadata[ 'userId' ] ) ? true : false;

		# Disable password related special pages and hide preference option.
		if ( !$switchedUser ) {
			$disableSpecialPages += [ 'ChangePassword', 'PasswordReset' ];
			global $wgHiddenPrefs;
			$wgHiddenPrefs[] = 'password';
		}

		# Redirect to given remote logout url. Either by redirect after a normal
		# logout request to Special:UserLogout or by replacing the url in the logout
		# button when user switching is not allowed and therefore the special page for
		# logout is not accessible.
		#
		# When the special page UserLogout is accessible, then we have to distinguish
		# between internal and external redirects. For internal redirects we have to
		# use the `UserLogout` hook to redirect before the local session/cookie will
		# be deleted. Because with this session provider in place we would login the
		# user with the next request again anyway (so no need to destroy the session).
		# For external redirects we must delete the local session/cookie first,
		# therefore we use the `UserLogoutComplete` hook for these type of urls.
		if ( $this->userUrls && isset( $this->userUrls[ 'logout' ] ) ) {
			$url = $this->userUrls[ 'logout' ];
			if ( $this->canChangeUser() ) {
				Hooks::register(
					'UserLogout',
					function() use ( $url, $metadata, $switchedUser ) {
						if ( $url instanceof Closure ) {
							$url = call_user_func( $url, $metadata );
						}
						$internal = Title::newFromText( $url );
						$known = $internal && $internal->isKnown();
						if ( $known ) {
							$url = $internal->getFullURL();
						}
						if ( $known && !$switchedUser ) {
							# Inhibit redirect loop.
							if ( !preg_match( '#[/=]Special:UserLogout([/?&].*)?$#', $url ) ) {
								global $wgOut;
								$wgOut->redirect( $url );
							}
							return false;
						}
						Hooks::register(
							'UserLogoutComplete',
							function() use ( $url ) {
								global $wgOut;
								$wgOut->redirect( $url );
								return true;
							}
						);
						return true;
					}
				);
			} else {
				Hooks::register(
					'PersonalUrls',
					function( &$personalurls ) use ( $url, $metadata ) {
						if ( $url instanceof Closure ) {
							$url = call_user_func( $url, $metadata );
						}
						$internal = Title::newFromText( $url );

						if ( $internal && $internal->isKnown() ) {
							$url = $internal->getLinkURL();
						}
						$personalurls[ 'logout' ][ 'href' ] = $url;
						return true;
					}
				);
			}
		} elseif ( !$switchedUser ) {
			$disablePersonalUrls[] = 'logout';
		}

		# Set user preferences on account creation only.
		if ( !$info->getUserInfo()->getId() ) {
			$prefs = $preferences;
			if ( $this->userPrefs ) {
				$prefs += $this->userPrefs;
			}
			Hooks::register(
				'LocalUserCreated',
				function( $user, $autoCreated ) use ( $prefs, $metadata ) {
					if ( $autoCreated ) {
						$this->setUserPrefs(
							$user,
							$prefs,
							$metadata
						);
					}
				}
			);
		}

		# Set user preferences on each request.
		#
		# Forcing user preferences is useful if they are provided by an external
		# source and must not be changed by the user himself.
		#
		# @see $wgGroupPermissions['user']['editmyoptions']
		if ( !$switchedUser && count( $preferences ) ) {

			$this->setUserPrefs(
				$info->getUserInfo()->getUser(),
				$preferences,
				$metadata,
				true
			);

			# Disable special pages related to email preferences.
			if ( array_key_exists( 'email', $preferences ) ) {
				$disableSpecialPages += [
					'ChangeEmail',
					'Confirmemail',
					'Invalidateemail'
				];
			}

			# Do not hide forced preferences completely by using the global
			# `$wgHiddenPrefs`, because we still want them to be shown to the user.
			# Therefore use the according hook to disable their editing capabilities.
			$keys = array_keys( $preferences );
			Hooks::register(
				'GetPreferences',
				function( $user, &$prefs ) use ( $keys ) {
					foreach( $keys as $key ) {

						if ( 'email' === $key ) {
							$key = 'emailaddress';
						}

						if ( !array_key_exists( $key, $prefs ) ) {
							continue;
						}

						# Email preference needs special treatment, because it will display a
						# link to change the address. We have to replace that with the address
						# only.
						if ( 'emailaddress' === $key ) {
							$prefs[ $key ][ 'default' ] = $user->getEmail() ?
								htmlspecialchars( $user->getEmail() ) : '';
						}

						$prefs[ $key ][ 'disabled' ] = 'disabled';

					}
				}
			);
		}

		# Don't remove anything.
		if ( !$this->removeAuthPagesAndLinks ) {
			$disableSpecialPages = [];
			$disablePersonalUrls = [];
		}

		Hooks::register(
			'SpecialPage_initList',
			function ( &$specials ) use ( $disableSpecialPages ) {
				foreach ( $disableSpecialPages as $page ) {
					unset( $specials[ $page ] );
				}
				return true;
			}
		);

		Hooks::register(
			'PersonalUrls',
			function ( &$personalurls ) use ( $disablePersonalUrls ) {
				foreach ( $disablePersonalUrls as $url ) {
					unset( $personalurls[ $url ] );
				}
				return true;
			}
		);

		return true;
	}

	/**
	 * We do support user switching (as inherited by our parent).
	 *
	 * This setting let us support the behaviour of Auth_remoteuser versions prior
	 * 2.0.0, where switching the logged-in local user (as denoted by the wiki
	 * database) wasn't possible.
	 *
	 * User switching is useful when your remote user is tied to a local wiki user,
	 * but needs access as another local user, e.g. a bot account, which in itself
	 * can never be identified as any remote user.
	 *
	 * @since 2.0.0
	 */
	public function canChangeUser() {
		return ( $this->switchUser ) ? parent::canChangeUser() : false;
	}

	/**
	 * Mark the cookie with current remote token.
	 *
	 * @since 2.0.0
	 */
	protected function cookieDataToExport( $user, $remember ) {
		return [ 'RemoteToken' => $this->remoteToken ] +
			parent::cookieDataToExport( $user, $remember );
	}

	/**
	 * Specify remote token.
	 *
	 * @since 2.0.0
	 */
	public function persistSession( SessionBackend $session, WebRequest $request ) {
		$metadata = $session->getProviderMetadata();
		$this->remoteToken = $metadata[ 'filteredUserName' ];
		return parent::persistSession( $session, $request );
	}

	/**
	 * Helper method to supplement (new local) users with additional information.
	 *
	 * The `$preferences` parameter contains an array of key => value pairs, where
	 * the keys `realname` and `email` are taken for the users real name and email
	 * address. All other keys in that array will be handled as an option into the
	 * users preference settings. Each value can also be of type Closure to get
	 * called when the value is evaluated. This type of late binding should then
	 * return the real value and could be useful, if you want to delegate the
	 * execution of code to a point where it is really needed and not inside
	 * `LocalSettings.php`. The first parameter given to such an anonymous function
	 * is an associative array with the following keys:
	 * * `userId` - id of user in local wiki database or 0 if new/anonymous
	 * * `remoteUserName` - value as given by the remote source
	 * * `filteredUserName` - after running hook for filtering of user names
	 * * `canonicalUserName` - representation in the local wiki database
	 * * `canonicalUserNameUsed` - the user name used for the current session
	 *
	 * @param User $user
	 * @param Array $preferences
	 * @param Array $metadata
	 * @param boolean $saveToDB Save changes to database with this funtion call.
	 * @see OauthUser::setRealName()
	 * @see OauthUser::setEmail()
	 * @see OauthUser::setOption()
	 * @since 2.0.0
	 */
	public function setUserPrefs( $user, $preferences, $metadata, $saveToDB = false ) {

		if ( $user instanceof User && is_array( $preferences ) && is_array( $metadata ) ) {

			# Mark changes to prevent superfluous database writings.
			$dirty = false;

			foreach ( $preferences as $option => $value ) {

				# If the given value is a closure, call it to get the value. All of our
				# provider metadata is exposed to this function as first parameter.
				if ( $value instanceof Closure ) {
					$value = call_user_func( $value, $metadata );
				}

				switch ( $option ) {
					case 'realname':
						if ( is_string( $value ) && $value !== $user->getRealName() ) {
							$dirty = true;
							$user->setRealName( $value );
						}
						break;
					case 'email':
						if ( Sanitizer::validateEmail( $value ) && $value !== $user->getEmail() ) {
							$dirty = true;
							$user->setEmail( $value );
							$user->confirmEmail();
						}
						break;
					default:
						if ( $value != $user->getOption( $option ) ) {
							$dirty = true;
							$user->setOption( $option, $value );
						}
				}
			}

			# Only update database if something has changed.
			if ( $saveToDB && $dirty ) {
				$user->saveSettings();
			}
		}

	}

}

