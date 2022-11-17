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

namespace GO\Dav\DavAcl;


class PrincipalBackend extends \Sabre\DAVACL\PrincipalBackend\AbstractBackend {

	private function _modelToDAVUser(\GO\Base\Model\User $user){

		$data= array(
			'id'=>$user->id,
			'uri'=>'principals/'.$user->username,
			'{DAV:}displayname' => $user->username,
			'{http://sabredav.org/ns}email-address'=>$user->email,
			'{urn:ietf:params:xml:ns:caldav}calendar-home-set' => new \Sabre\DAV\Xml\Property\Href('calendars/'.$user->username),
			'{urn:ietf:params:xml:ns:caldav}schedule-inbox-URL'=>new \Sabre\DAV\Xml\Property\Href('principals/'.$user->username.'/inbox'),
			'{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL'=>new \Sabre\DAV\Xml\Property\Href('principals/'.$user->username.'/outbox')
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
     * @param StringHelper $prefixPath 
     * @return array 
     */
    public function getPrincipalsByPrefix($prefixPath) {

		\GO::debug('GO\DAV\Auth\Backend::getUsers()');

		if (!isset($this->users)) {
			$this->users = array($this->_modelToDAVUser(\GO::user()));
		}
		return $this->users;
	}

    /**
     * Returns a specific principal, specified by it's path.
     * The returned structure should be the exact same as from 
     * getPrincipalsByPrefix. 
     * 
     * @param StringHelper $path 
     * @return array 
     */
		public function getPrincipalByPath($path) {

		//path can be principals/username or
		//principals/username/calendar-proxy-write
		//we ignore principals and the second element is our username.

			$pathParts = explode('/', $path);
			
			//var_dump($pathParts);


			$username = $pathParts[1];
			
			\GO::debug("getPrincipalByPath($path)");

			$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
			if (!$user) {
				return false;
			} elseif (isset($pathParts[2])) {
				return array(
						'uri' => $path,
						'{DAV:}displayname' => $pathParts[2]
				);
			} else {
				return $this->_modelToDAVUser($user);
			}
		}

    /**
     * Returns the list of members for a group-principal 
     * 
     * @param StringHelper $principal 
     * @return array 
     */
    public function getGroupMemberSet($principal) {

			\GO::debug("getGroupMemberSet($principal)");
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
     * @param StringHelper $principal 
     * @return array 
     */
    public function getGroupMembership($principal) {
			\GO::debug("getGroupMemberSet($principal)");
			
			return array();
//        $principal = $this->getPrincipalByPath($principal);
//        if (!$principal) throw new Sabre\DAV\Exception('Principal not found');
//
//        $stmt = $this->pdo->prepare('SELECT principals.uri as uri FROM groupmembers LEFT JOIN principals ON groupmembers.principal_id = principals.id WHERE groupmembers.member_id = ?');
//        $stmt->execute(array($principal['id']));
//
//        $result = array();
//        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            $result[] = $row['uri'];
//        }
//        return $result;

    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's. 
     * 
     * @param StringHelper $principal 
     * @param array $members 
     * @return void
     */
    public function setGroupMemberSet($principal, array $members) {
			\GO::debug("setGroupMemberSet($principal)");
        // Grabbing the list of principal id's.
//        $stmt = $this->pdo->prepare('SELECT id, uri FROM principals WHERE uri IN (? ' . str_repeat(', ? ', count($members)) . ');');
//        $stmt->execute(array_merge(array($principal), $members));
//
//        $memberIds = array();
//        $principalId = null;
//
//        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            if ($row['uri'] == $principal) {
//                $principalId = $row['id'];
//            } else {
//                $memberIds[] = $row['id'];
//            }
//        }
//        if (!$principalId) throw new Sabre\DAV\Exception('Principal not found');
//
//        // Wiping out old members
//        $stmt = $this->pdo->prepare('DELETE FROM groupmembers WHERE principal_id = ?;');
//        $stmt->execute(array($principalId));
//
//        foreach($memberIds as $memberId) {
//
//            $stmt = $this->pdo->prepare('INSERT INTO groupmembers (principal_id, member_id) VALUES (?, ?);');
//            $stmt->execute(array($principalId, $memberId));
//
//        }

    }
		
		function updatePrincipal($path, \Sabre\DAV\PropPatch $mutations){
			return false;
		}
		
		function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof') {
			
			\GO::debug("searchPrincipals");

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('t.username');
		$findCriteria = $findParams->getCriteria();

		foreach ($searchProperties as $property => $value) {

			switch ($property) {

				case '{DAV:}displayname' :

					$findCriteria->addRawCondition("CONCAT(t.first_name,t.middle_name,t.last_name)", ":name", "LIKE");
					$findCriteria->addBindParameter(":name", '%' . $value . '%');

					break;
				case '{http://sabredav.org/ns}email-address' :
					$findCriteria->addCondition('email', '%' . $value . '%', 'LIKE');
					break;
				default :
					// Unsupported property
					return array();
			}
		}
		
		$stmt = \GO\Base\Model\User::model()->find($findParams);

		$principals = array();
		while ($record = $stmt->fetch()) {
			// Checking if the principal is in the prefix
//			list($rowPrefix) = Sabre\DAV\URLUtil::splitPath($row['uri']);
//			if ($rowPrefix !== $prefixPath)
//				continue;

			$principals[] = $record->username;
		}

		return $principals;
	}


}
