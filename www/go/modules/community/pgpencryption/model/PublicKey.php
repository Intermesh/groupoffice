<?php

namespace go\modules\community\pgpencryption\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\pgpencryption\convert\Key;

class PublicKey extends Entity
{
	public $id;

	public $key;

	public $createdAt;

	public string $contactId;

	public $email;

	public $expiresAt;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("pgp_public_key", "pgppublic");
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add("id", function (Criteria $criteria, $value) {
				$criteria->andWhere("id", "=", $value);
			})
			->add("contactId", function (Criteria $criteria, $value) {
				$contactId = Contact::findForUser($value)->id;

				$criteria->andWhere("contactId", "=", $contactId);
			})
			->add("email", function (Criteria $criteria, $value) {
				$criteria->andWhere("email", "=", $value);
			});
	}

	protected function internalSave(): bool
	{

		if (!isset($this->contactId)) {
			$contactId = Contact::findForUser(go()->getUserId())->id;
		}

		if (isset($contactId)) {
			$this->contactId = $contactId;
		}

		return parent::internalSave();
	}

	protected static function internalDelete(Query $query): bool
	{
		PrivateKey::delete(
			(new Query())->where('publicKeyId', 'IN', (clone $query)->select('id'))
		);

		return parent::internalDelete($query);
	}

	public static function converters(): array
	{
		return array_merge(parent::converters(), [Key::class]);
	}
}