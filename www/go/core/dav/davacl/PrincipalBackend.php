<?php

/**
 * PDO principal backend
 *
 * This is a simple principal backend that maps exactly to the users table, as 
 * used by Sabre\DAV\Auth_Backend\PDO.
 *
 * It assumes all principals are in a single collection. The default collection 
 * is 'principals/', but this can be overriden.
 *
 * @package Sabre
 * @subpackage DAVACL
 * @copyright Copyright (C) 2007-2011 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */

namespace go\core\dav\davacl;

use go\core\model\Acl;
use go\core\model\User;
use Sabre\DAV\PropPatch;
use Sabre\DAV\Xml\Property\Href;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;
use Sabre\Uri;

class PrincipalBackend extends AbstractBackend {

	private function modelToDAVUser(User $user) {
		return [
			'id' => $user->id,
			'uri' => 'principals/' . $user->username,
			'{DAV:}displayname' => $user->displayName,
			'{http://sabredav.org/ns}email-address' => $user->email,
			'{urn:ietf:params:xml:ns:caldav}calendar-home-set' => new Href('calendars/' . $user->username),
			'{urn:ietf:params:xml:ns:caldav}schedule-inbox-URL' => new Href('principals/' . $user->username . '/inbox'),
			'{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL' => new Href('principals/' . $user->username . '/outbox')
		];
	}
	private $users;

	/**
	 * Returns a list of principals based on a prefix.
	 *
	 * This prefix will often contain something like 'principals'. You are only 
	 * expected to return principals that are in this base path.
	 *
	 * You are expected to return at least a 'uri' for every user, you can 
	 * return any additional properties if you wish so. Common properties are:
	 *   {DAV:}displayname 
	 *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV 
	 *     field that's actualy injected in a number of other properties. If
	 *     you have an email address, use this property.
	 * 
	 * @param string $prefixPath 
	 * @return array 
	 */
	public function getPrincipalsByPrefix($prefixPath) {

		go()->debug('PrincipalBackend::getPrincipalsByPrefix('.$prefixPath.')');

		if (!isset($this->users)) {
//			$this->users = [];
//			 $users = User::find(['id', 'username', 'displayName', 'email'])
//				 ->filter(['permissionLevel' => Acl::LEVEL_READ]);
//
//			 foreach($users as $user) {
//				$this->users[] = $this->modelToDAVUser($user);
//			 }

			// don't list all users for privacy reasons
			$this->users = [$this->modelToDAVUser(go()->getAuthState()->getUser(['id', 'username', 'displayName', 'email']))];
		}
		return $this->users;
	}

	/**
	 * Returns a specific principal, specified by it's path.
	 * The returned structure should be the exact same as from 
	 * getPrincipalsByPrefix. 
	 * 
	 * @param string $path 
	 * @return array | void
	 */
	public function getPrincipalByPath($path) {

		go()->debug('PrincipalBackend::getPrincipalByPath('.$path.')');

		// path can be principals/username or
		// principals/username/calendar-proxy-write
		// we ignore principals and the second element is our username.

		$pathParts = explode('/', $path);

		$username = $pathParts[1];

		go()->debug("getPrincipalByPath($path)");

		$user = User::find(['id', 'username', 'displayName', 'email'])->where('username', '=', $username)->single();
		if (!$user) {
			return;
		}
		if (isset($pathParts[2])) {
			return [
				'uri' => $path,
				'{DAV:}displayname' => $pathParts[2]
			];
		}
		return $this->modelToDAVUser($user);

	}

	/**
	 * Returns the list of members for a group-principal 
	 * 
	 * @param string $principal 
	 * @return array 
	 */
	public function getGroupMemberSet($principal) {
		go()->debug("PrincipalBackend::getGroupMemberSet($principal)");
		return [];
	}

	/**
	 * Returns the list of groups a principal is a member of 
	 * 
	 * @param string $principal 
	 * @return array 
	 */
	public function getGroupMembership($principal) {
		go()->debug("PrincipalBackend::getGroupMemberSet($principal)");
		return [];
	}

	/**
	 * Updates the list of group members for a group principal.
	 *
	 * The principals should be passed as a list of uri's. 
	 * 
	 * @param string $principal 
	 * @param array $members 
	 * @return void
	 */
	public function setGroupMemberSet($principal, array $members) {
		go()->debug("PrincipalBackend::setGroupMemberSet($principal)");
	}

	function updatePrincipal($path, PropPatch $mutations) {
		return false;
	}

	function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof') {

		go()->debug("PrincipalBackend::searchPrincipals($prefixPath, ". var_export($searchProperties, true) . ', ' . $test .')');

		if($prefixPath != "principals") {
			return [];
		}

		$query = User::find(['username'])
			->filter(['permissionLevel' => Acl::LEVEL_READ])
			->selectSingleValue('username');

		foreach ($searchProperties as $property => $value) {

			switch ($property) {
				case '{DAV:}displayname' :
					if($test == "allof") {
						$query->where('displayName', 'LIKE', '%' . $value . '%');
					} else {
						$query->orWhere('displayName', 'LIKE', '%' . $value . '%');
					}
					break;

				case '{http://sabredav.org/ns}email-address':
					if($test == "allof") {
						$query->where('email', 'LIKE', '%' . $value . '%');
					} else {
						$query->orWhere('email', 'LIKE', '%' . $value . '%');
					}
					break;
				default :
					// Unsupported property
					return [];
			}
		}

//		go()->debug($query);

		$principals = [];
		foreach($query as $username) {			
			$principals[] = 'principals/' . $username;
		}

		go()->debug("Found ". count($principals) ." principals");

		return $principals;
	}



	/**
	 * Finds a principal by its URI.
	 *
	 * This method may receive any type of uri, but mailto: addresses will be
	 * the most common.
	 *
	 * Implementation of this API is optional. It is currently used by the
	 * CalDAV system to find principals based on their email addresses. If this
	 * API is not implemented, some features may not work correctly.
	 *
	 * This method must return a relative principal path, or null, if the
	 * principal was not found or you refuse to find it.
	 *
	 * This method has to be implemented otherwise caldav scheduling will use searchPrincipals above which
	 * may find incorrect principals and schedule's in the wrong calendar
	 *
	 * @param string $uri
	 * @param string $principalPrefix
	 *
	 * @return string
	 */
	public function findByUri($uri, $principalPrefix)
	{
		go()->debug("PrincipalBackend::findByUri($uri, $principalPrefix)");

		if($principalPrefix != "principals") {
			return null;
		}

		$uriParts = Uri\parse($uri);

		// Only two types of uri are supported :
		//   - the "mailto:" scheme with some non-empty address
		//   - a principals uri, in the form "principals/NAME"
		// In both cases, `path` must not be empty.
		if (empty($uriParts['path'])) {
			return null;
		}

		if ('mailto' === $uriParts['scheme']) {
			$username = User::find(['username'])
				->filter(['permissionLevel' => Acl::LEVEL_READ])
				->selectSingleValue('username')
				->where('email', 'LIKE', $uriParts['path'])
				->single();

			if(!$username) {
				return null;
			}

			go()->debug("Found: " . $username);

			return "principals/".$username;
		} else {
			$pathParts = Uri\split($uriParts['path']); // We can do this since $uriParts['path'] is not null

			if (2 === count($pathParts) && $pathParts[0] === $principalPrefix) {
				$username = User::find(['username'])
					->filter(['permissionLevel' => Acl::LEVEL_READ])
					->selectSingleValue('username')
					->where('username', 'LIKE', $uriParts['path'])
					->single();

				if(!$username) {
					return null;
				}

				go()->debug("Found: " . $username);

				return "principals/".$username;
			} else {
				return null;
			}
		}
	}

}
