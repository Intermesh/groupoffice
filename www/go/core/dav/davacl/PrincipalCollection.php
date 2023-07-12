<?php
namespace go\core\dav\davacl;


use Sabre\DAVACL\AbstractPrincipalCollection;

class PrincipalCollection extends AbstractPrincipalCollection {

	/**
	 * Returns a child object based on principal information.
	 *
	 * @return User
	 */
	public function getChildForPrincipal(array $principalInfo)
	{
		return new User($this->principalBackend, $principalInfo);
	}
}