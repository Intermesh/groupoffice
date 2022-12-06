<?php
namespace go\core\dav\davacl;

class User extends \Sabre\CalDAV\Principal\User {
	public function getACL()
	{
		$acl = parent::getACL();

		// add read privileges for authenticated users
		$acl[] = [
			'privilege' => '{DAV:}read',
			'principal' => '{DAV:}authenticated',
			'protected' => true,
		];

		return $acl;
	}
}