<?php
namespace go\core\model;

use Exception;
use go\core\jmap\Entity;
use go\core\orm\Mapping;
use go\core\util\IpAddress;

/**
 * The Acl class
 *
 * Is an Access Control List to restrict access to data.
 */
class AuthAllowGroup extends Entity
{
  /**
   * Primary key
   *
   */
  public ?string $id;

  /**
   * Group ID
   *
   */
  public string $groupId;

  /**
   * IP Address. Wildcards can be used where * matches anything and ? matches exactly one character.
   *
   * @var string
   */
  public $ipPattern;

  protected static function defineMapping(): Mapping
  {
    return parent::defineMapping()
      ->addTable('core_auth_allow_group', 'ag');
  }

	/**
   * Check if a user is allowed for a given IP address
   *
   * @param User $user
   * @param string $ip
   * @return bool
   * @throws Exception
   */
  public static function isAllowed(User $user, $ip) {

    $patterns = self::find()->selectSingleValue('ipPattern')
      ->join('core_user_group', 'ug', 'ug.groupId = ag.groupId')
      ->where('ug.userId', '=',  $user->id)
	    ->execute();

    if(!$patterns->rowCount()) {
    	return true;
    }

    foreach($patterns as $pattern) {
      if(self::match($ip, $pattern)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Match the pattern with IP address
   *
   * Supports exact addresses, wildcards (* and ?) and CIDR notation.
   *
   * @param string $ip
   * @param string $pattern
   * @return bool
   */
  private static function match(string $ip, string $pattern): bool {
    $pattern = trim($pattern);
    if ($pattern === '') {
      return false;
    }

    if (str_contains($pattern, '/')) {
      return IpAddress::matches($ip, $pattern);
    }

    $regex = str_replace(['\*', '\?'], ['.*', '.'], preg_quote($pattern, '/'));

    return (bool) preg_match('/^' . $regex . '$/Di', $ip);
  }
}
