<?php

namespace go\modules\community\webpush\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;

class PushSubscription extends Entity {

	public ?string $id;

	public ?string $userId;
	public ?string $deviceClientId;

	/* endpoint to send notification to */
	public ?string $url;

	public ?\DateTime $expires;

	public ?string $createdBy;

	protected ?string $p256dh; // public key
	protected ?string $auth; // auth token
	protected ?string $verificationCode;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_push_subscription");
	}

	public function getKeys() {
		return ['p256dh' => $this->p256dh, 'auth' => $this->auth];
	}

	public function publicKey() {
		return $this->p256dh;
	}
	public function authToken() {
		return $this->auth;
	}

	public function setKeys($keys){
		$this->p256dh = $keys['p256dh'];
		$this->auth = $keys['auth'];
	}

	public function setVerificationCode($code) {
		if($this->verificationCode === $code){
			$this->verificationCode = null;
			$this->expires = (new DateTime())->add(new \DateInterval('P7D'));
		} else {
			throw new \InvalidArgumentException("Invalid verification code");
		}
	}

	public function isVerified() {
		return $this->verificationCode === null;
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('default', function (Criteria $criteria, $value, Query $query) {
			$query->andWhere('createdBy', '=', go()->getAuthState()->getUserId())
			->andWhere('expires', '>', new DateTime());
		}, 'alwaysUsed');
	}

	public function internalSave() : bool {
		if($this->isNew()) {
			$this->verificationCode = $this->generateVerificationCode();
			$this->expires = (new DateTime())->add(new \DateInterval('PT1H'));
			$wasNew = true;
		}
		$success = parent::internalSave();
		if($success && !empty($wasNew)) {
			$this->sendVerificationCode();
		}
		return $success;
	}

	public function sendVerificationCode() {
		if($this->verificationCode !== null)
		(new WebPush())->queue($this, json_encode([
			"pushSubscriptionId" => $this->id,
			"verificationCode" => $this->verificationCode
		]))->flush();
	}

	private function generateVerificationCode() {
		return base64_encode(random_bytes(15));
	}
}