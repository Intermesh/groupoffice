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

use GO;
use go\core\model\Acl;
use go\core\model\User;
use Sabre\CalDAV\Principal\User as PrincipalUser;
use Sabre\DAV\PropPatch;
use Sabre\DAV\Xml\Property\Href;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;

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

		go()->debug('GO\DAV\Auth\Backend::getUsers()');

		if (!isset($this->users)) {
			$this->users = [];
			 $users = User::find(['id', 'username', 'displayName', 'email'])
				 ->filter(['permissionLevel' => Acl::LEVEL_READ]);

			 foreach($users as $user) {
				$this->users[] = $this->modelToDAVUser($user);
			 }
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
		go()->debug("getGroupMemberSet($principal)");
		return [];
	}

	/**
	 * Returns the list of groups a principal is a member of 
	 * 
	 * @param string $principal 
	 * @return array 
	 */
	public function getGroupMembership($principal) {
		go()->debug("getGroupMemberSet($principal)");
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
		go()->debug("setGroupMemberSet($principal)");
	}

	function updatePrincipal($path, PropPatch $mutations) {
		return false;
	}

	function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof') {

		go()->debug("searchPrincipals($prefixPath, ". var_export($searchProperties, true) . ', ' . $test .')');

		if($prefixPath != "principals") {
			return [];
		}

		$query = User::find(['username'])
			->filter(['permissionLevel' => Acl::LEVEL_READ])
			->selectSingleValue('username');

		foreach ($searchProperties as $property => $value) {

			switch ($property) {
				case '{DAV:}displayname' :					
					$query->where('displayName', 'LIKE', '%' . $value . '%');
					break;

				case '{http://sabredav.org/ns}email-address':
					$query->where('email', 'LIKE', '%' . $value . '%');
					break;
				default :
					// Unsupported property
					return [];
			}
		}
		
		$principals = [];
		foreach($query as $username) {			
			$principals[] = 'principals/' . $username;
		}

		go()->debug("Found ". count($principals) ." principals");

		return $principals;
	}

}
