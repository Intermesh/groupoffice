<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Relation;
use go\core\util\DateTime;

class AppPassword extends Entity
{
	public ?string $id;

	public string $userId;

	public string $label;

	protected string $passwordHash;

	public DateTime $createdAt;

	public ?DateTime $lastUsedAt;

	public ?string $lastUsedIp;

	public ?DateTime $revokedAt;

	/** @var AppPasswordScope[] */
	public array $scopes;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_app_password", "cap")
			->add("scopes", Relation::array(AppPasswordScope::class)->keys(['id' => 'appPasswordId']));
	}

	public static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('user', function (Criteria $criteria, $value) {
				$criteria->where('userId', '=', $value);
			})
			->add('isRevoked', function (Criteria $criteria, $value) {
				if ($value) {
					$criteria->andWhere('revokedAt', '!=', null);
				} else {
					$criteria->andWhere('revokedAt', '=', null);
				}
			});
	}

	public function getPasswordHash()
	{
		return null;
	}

	public function setPasswordHash(string $secret): void
	{
		$this->passwordHash = password_hash($secret, PASSWORD_DEFAULT);
	}

	public function hasMatchingScope(string $protocol): bool
	{
		foreach ($this->scopes as $scope) {
			if ($scope->protocol == $protocol) {
				return true;
			}
		}

		return false;
	}

	public function verifyPassword(string $password): bool
	{
		return password_verify($password, $this->passwordHash);
	}

	public function updateLastUsed(string $ipAddress)
	{
		$saveChanges = false;

		if ($ipAddress !== $this->lastUsedIp) {
			$this->lastUsedIp = $ipAddress;

			$saveChanges = true;
		}

		// only update lastUsedAt once per day
		$now = new DateTime();
		if (!$this->lastUsedAt || $this->lastUsedAt->format('Y-m-d') !== $now->format('Y-m-d')) {
			$this->lastUsedAt = $now;

			$saveChanges = true;
		}

		if ($saveChanges) {
			$this->save();
		}
	}

}