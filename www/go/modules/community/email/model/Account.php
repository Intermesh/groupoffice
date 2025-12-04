<?php

namespace go\modules\community\email\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * @property \go\modules\community\oauth2client\model\Oauth2Account|null $oauth2_account
 */
class Account extends AclOwnerEntity
{

	public ?string $id;
	public string $username;
	public $password;
	public $smtp_username;
	public $smtp_password;
	public $acl_id;
	public $force_smtp_login;
	public $password_encrypted;
	public static string $aclColumnName = 'acl_id';

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('em_accounts')
			->addQuery((new \go\core\db\Query)->select('acl_id AS aclId')); //temporary hack
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->addText('username', function (Criteria $criteria, $comparator, $value, Query $query, array $filters) {
				$criteria->where('username', '=', $value);
			});
	}

	/**
	 * Override the default as the ACL field is named differently. Sigh.
	 *
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId
	 * @param array|null $groups
	 * @return Query
	 * @throws \go\core\exception\Forbidden
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int $userId = null, array $groups = null): Query
	{
		$col = static::getMapping()->getColumn(static::$aclColumnName);
		$tableAlias = $col->table->getAlias();
		Acl::applyToQuery($query, $tableAlias . '.' . static::$aclColumnName, $level, $userId, $groups);

		return $query;
	}

	protected function internalSave(): bool
	{
		if ($this->isModified('password')) {
			$encrypted = \GO\Base\Util\Crypt::encrypt($this->password);
			if($encrypted){
				$this->password = $encrypted;
				$this->password_encrypted=2;//deprecated. remove when email is mvc style.
			}
		}

		if ($this->isModified('smtp_password') && strlen($this->smtp_password) > 0) {
			$encrypted = \GO\Base\Util\Crypt::encrypt($this->smtp_password);
			if($encrypted) {
				$this->smtp_password = $encrypted;
			}
		}
		return parent::internalSave();
	}

	public function title(): string
	{
		return $this->username;
	}
}
