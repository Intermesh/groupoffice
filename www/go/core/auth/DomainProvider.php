<?php
namespace go\core\auth;

/**
 * Use this interface for authenticators that can provide
 * authentication domains. 
 * For example LDAP or IMAP authenticator modules.
 * 
 * Note: These domains are cached. So when implementing new ones make sure to 
 * run install/upgrade.php to clear the cache.
 */
interface DomainProvider {
	/**
	 * Return authentication domains
	 * 
	 * @return string[]
	 */
	public static function getDomainNames(): array;
}
