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

	public string $id;
	public ?string $host;
	public ?string $username;
	public ?string $password;
	public ?string $smtp_username;
	public ?string $smtp_password;
	public string $acl_id;
	public bool $force_smtp_login = false;
	public bool $password_encrypted;
	public int $sieve_port = 2000; // As per default value NetSieve class
	public bool $sieve_usetls = true;
	public static string $aclColumnName = 'acl_id';

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("em_accounts")
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
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null): Query
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

	public function decryptPassword(): string
	{
		if (empty($this->password)) {
			return "";
		}
		return \GO\Base\Util\Crypt::decrypt($this->password);
	}
}
