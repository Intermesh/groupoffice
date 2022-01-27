<?php
namespace go\core\model;

use Exception;
use go\core\jmap\Entity;
use go\core\orm\Mapping;

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
   * @var int
   */
  public $id;

  /**
   * Group ID
   *
   * @var int
   */
  public $groupId;

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
   * @param string $ip
   * @param string $pattern
   * @return false|int
   */
  private static function match($ip, $pattern) {
    $pattern = str_replace('.', '\\.', $pattern);
    $pattern = str_replace('?', '.', $pattern);
    $pattern = str_replace('*', '.*', $pattern);

    return preg_match('/' . $pattern .'/', $ip);
  }
}