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
use go\core\model\User;
use Sabre\CalDAV\Principal\User as PrincipalUser;
use Sabre\DAV\PropPatch;
use Sabre\DAV\Xml\Property\Href;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;

class PrincipalBackend extends AbstractBackend {

	private function modelToDAVUser(User $user) {

		$data = array(
				'id' => $user->id,
				'uri' => 'principals/' . $user->username,
				'{DAV:}displayname' => $user->displayName,
				'{http://sabredav.org/ns}email-address' => $user->email,
				'{urn:ietf:params:xml:ns:caldav}calendar-home-set' => new Href('calendars/' . $user->username),
				'{urn:ietf:params:xml:ns:caldav}schedule-inbox-URL' => new Href('principals/' . $user->username . '/inbox'),
				'{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL' => new Href('principals/' . $user->username . '/outbox')
		);

		return $data;
	}

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
			//$this->users = [];
			// $users = User::find(['id', 'username', 'displayName', 'email']);
			// foreach($users as $user) {
				$this->users = [$this->modelToDAVUser(go()->getAuthState()->getUser(['id', 'username', 'displayName', 'email']))];
			// }
		}
		return $this->users;
	}

	/**
	 * Returns a specific principal, specified by it's path.
	 * The returned structure should be the exact same as from 
	 * getPrincipalsByPrefix. 
	 * 
	 * @param string $path 
	 * @return array 
	 */
	public function getPrincipalByPath($path) {

		//path can be principals/username or
		//principals/username/calendar-proxy-write
		//we ignore principals and the second element is our username.

		$pathParts = explode('/', $path);

		//var_dump($pathParts);


		$username = $pathParts[1];

		go()->debug("getPrincipalByPath($path)");

		$user = User::find(['id', 'username', 'displayName', 'email'])->where('username', '=', $username)->single();
		if (!$user) {
			return false;
		} elseif (isset($pathParts[2])) {
			return array(
					'uri' => $path,
					'{DAV:}displayname' => $pathParts[2]
			);
		} else {
			return $this->modelToDAVUser($user);
		}
	}

	/**
	 * Returns the list of members for a group-principal 
	 * 
	 * @param string $principal 
	 * @return array 
	 */
	public function getGroupMemberSet($principal) {

		go()->debug("getGroupMemberSet($principal)");
//        $principal = $this->getPrincipalByPath($principal);
//        if (!$principal) throw new Sabre\DAV\Exception('Principal not found');
//
//        $stmt = $this->pdo->prepare('SELECT principals.uri as uri FROM groupmembers LEFT JOIN principals ON groupmembers.member_id = principals.id WHERE groupmembers.principal_id = ?');
//        $stmt->execute(array($principal['id']));
//
//        $result = array();
//        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            $result[] = $row['uri'];
//        }
//        return $result;

		return array();
	}

	/**
	 * Returns the list of groups a principal is a member of 
	 * 
	 * @param string $principal 
	 * @return array 
	 */
	public function getGroupMembership($principal) {
		go()->debug("getGroupMemberSet($principal)");

		return array();
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

		go()->debug("searchPrincipals");
		
		$query = go()->getDbConnection()
						->selectSingleValue('username')
						->from('core_user');

		foreach ($searchProperties as $property => $value) {

			switch ($property) {
				case '{DAV:}displayname' :					
					$query->where('displayName', 'LIKE', '%' . $value . '%');
					break;
				
				case '{http://sabredav.org/ns}email-address' :
					$query->where('email', 'LILE', '%' . $value . '%');					
					break;
				default :
					// Unsupported property
					return [];
			}
		}
		
		$principals = [];
		foreach($query as $username) {			
			$principals[] = $username;
		}

		return $principals;
	}

	public function findByUri($uri, $principalPrefix) {
//		$value = null;
//        $scheme = null;
//        list($scheme, $value) = explode(":", $uri, 2);
//        if (empty($value)) return null;
//
//        $uri = null;
//        switch ($scheme){
//            case "mailto":
//                $query = 'SELECT uri FROM ' . $this->tableName . ' WHERE lower(email)=lower(?)';
//                $stmt = $this->pdo->prepare($query);
//                $stmt->execute([ $value ]);
//            
//                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
//                    // Checking if the principal is in the prefix
//                    list($rowPrefix) = URLUtil::splitPath($row['uri']);
//                    if ($rowPrefix !== $principalPrefix) continue;
//                    
//                    $uri = $row['uri'];
//                    break; //Stop on first match
//                }
//                break;
//            default:
//                //unsupported uri scheme
//                return null;
//        }
//        return $uri;
	}

}
