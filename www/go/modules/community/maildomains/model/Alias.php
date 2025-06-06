<?php

namespace go\modules\community\maildomains\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

final class Alias extends AclItemEntity
{
	public ?string $id;
	public ?string $domainId;
	public string $address;
	public string $goto;
	public ?string $createdBy;
	public ?\DateTimeInterface $createdAt;
	public ?string $modifiedBy;
	public ?\DateTimeInterface $modifiedAt;
	public bool $active = true;
	private ?Domain $domain = null;

	/**
	 * @inheritDoc
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_alias");
	}

	protected static function aclEntityClass(): string
	{
		return Domain::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['domainId' => 'id'];
	}

	public static function getClientName(): string
	{
		return "MailAlias";
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add("domainId", function (Criteria $criteria, $value) {
				$criteria->andWhere('domainId', '=', $value);
			});
	}

	/**
	 * @inheritDoc
	 */
	protected static function textFilterColumns(): array
	{
		return ['address', 'goto'];
	}

	/**
	 * @inheritDoc
	 */
	public function title(): string
	{
		return $this->address;
	}

	/**
	 * @inheritDoc
	 */
	public function internalValidate()
	{
		$d = $this->getDomain();
		$domainParts = explode('@', $this->address);

		if (isset($domainParts[1]) && ($domainParts[1] !== $d->domain)) {
			$this->setValidationError('address', "The domain part of the address must match with the main domain");
		}

		if (!empty($d->maxAliases) && $this->isNew() && $d->countAliases() + 1 > $d->maxAliases) {
			throw new Forbidden('The maximum number of mailboxes for this domain has been reached.');
		}
		parent::internalValidate();
	}

	/**
	 *
	 * @iinheritDoc
	 * @return bool
	 * @throws \Exception
	 */
	protected function internalSave(): bool
	{
		$d = $this->getDomain();

		//chop off wildcard because in db it must be @domain.com but we use *@domain.com
		if(substr($this->address, 0,2) == '*@') {
			$this->address = substr($this->address, 1);
		}


		return parent::internalSave();
	}


	/**
	 * Retrieve the domain
	 *
	 * As the ORM does currently not support retrieving its owner entity through a relation, we simply retrieve the
	 * entity by ID
	 * @return Domain|null
	 * @throws \Exception
	 */
	private function getDomain(): Domain|null
	{
		if(!isset($this->domainId) && isset($this->address)) {
			$domain = explode("@", $this->address)[1];

			$this->domain = Domain::find()->where(['domain' => $domain])->single();

			if($this->domain) {
				$this->domainId = $this->domain->id;
			}
		}

		if (!isset($this->domain) && isset($this->domainId)) {
			$this->domain = Domain::findById($this->domainId);
		}
		return $this->domain;
	}
}