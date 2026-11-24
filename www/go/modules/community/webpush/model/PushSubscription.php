<?php

namespace go\modules\community\webpush\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;

class PushSubscription extends Entity
{

	public ?string $id;

	public ?string $userId;
	public ?string $deviceClientId;

	/* endpoint to send notification to */
	public ?string $url;

	public ?\DateTime $expires;
	protected ?string $p256dh; // public key
	protected ?string $auth; // auth token
	protected ?string $verificationCode;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_push_subscription");
	}

	public function getKeys()
	{
		return ['p256dh' => $this->p256dh, 'auth' => $this->auth];
	}

	public function publicKey()
	{
		return $this->p256dh;
	}

	public function authToken()
	{
		return $this->auth;
	}

	public function setKeys($keys)
	{
		$this->p256dh = $keys['p256dh'];
		$this->auth = $keys['auth'];
	}

	public function setVerificationCode($code)
	{
		if ($this->verificationCode === $code) {
			$this->verificationCode = null;
			$this->expires = (new DateTime())->add(new \DateInterval('P7D'));
		} else {
			throw new \InvalidArgumentException("Invalid verification code");
		}
	}

	public function isVerified()
	{
		return $this->verificationCode === null;
	}

	/**
	 * True when the browser confirmed the subscription, so push messages reach it. Read-only for the client.
	 */
	public function getVerified(): bool
	{
		return $this->isVerified();
	}

	protected static function defineFilters(): Filters
	{
		// Expired subscriptions are replaced by the client
		return parent::defineFilters()->add('default', function (Criteria $criteria, $value, Query $query) {
			$query->andWhere('expires', '>', new DateTime());
		}, 'alwaysUsed')
			// The subscriptions of one browser
			->add('deviceClientId', function (Criteria $criteria, $value) {
				$criteria->where('deviceClientId', '=', $value);
			});
	}

	/**
	 * Users only see their own subscriptions
	 */
	public static function applyAclToQuery(\go\core\orm\Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null): \go\core\orm\Query
	{
		$query->andWhere($query->getTableAlias() . '.userId', '=', $userId ?? go()->getAuthState()->getUserId());
		return $query;
	}

	/**
	 * Every user may subscribe their own devices, and only change their own subscriptions
	 */
	protected function internalGetPermissionLevel(): int
	{
		$userId = go()->getAuthState()->getUserId();
		if (!$userId) {
			return 0;
		}
		if ($this->isNew()) {
			return Acl::LEVEL_MANAGE;
		}
		return (string) $this->userId === (string) $userId ? Acl::LEVEL_MANAGE : 0;
	}

	protected function canCreate(): bool
	{
		return go()->getAuthState()->isAuthenticated();
	}

	/**
	 * The subscriptions that may receive push messages: verified and not expired. For the cron job, which has no user.
	 *
	 * @return self[]
	 */
	public static function findActiveForUser(int|string $userId): array
	{
		$query = static::internalFind([], true)
			->where('userId', '=', $userId)
			->andWhere('verificationCode', 'IS', null)
			->andWhere('expires', '>', new DateTime());
		return $query->all();
	}

	public function internalSave(): bool
	{
		if ($this->isNew()) {
			// A user can only subscribe for themselves
			$this->userId = (string) go()->getAuthState()->getUserId();
			$this->verificationCode = $this->generateVerificationCode();
			$this->expires = (new DateTime())->add(new \DateInterval('PT1H'));
			$wasNew = true;
		}
		$success = parent::internalSave();
		if ($success && !empty($wasNew)) {
			$this->sendVerificationCode();
		}
		return $success;
	}

	public function sendVerificationCode()
	{
		if ($this->verificationCode !== null)
			(new WebPush())->queue($this, json_encode([
				"pushSubscriptionId" => $this->id,
				"verificationCode" => $this->verificationCode
			]))->flush();
	}

	private function generateVerificationCode()
	{
		return base64_encode(random_bytes(15));
	}
}