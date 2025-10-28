<?php

namespace go\core\model;

use go\core\Environment;
use go\core\jmap\Request;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\util\DateTime;

class Client extends Property
{
	public ?int $id;
	public $deviceId = '-';
	public $platform;
	public $name;
	public $version;
	public $ip;
	public $lastSeen;
	public $createdAt;
	public $status;
	public $userId;
	public $needResync;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_client', 'r');
	}

	public function save() {
		if(!$this->isNew() && $this->isModified('status')) {
			$this->needResync = true;
		}
		$success = $this->internalSave();
//		if($success) {
//			$this->owner->change(true);
//		}
		return $success;
	}

	public function isAllowed() : bool {
		return $this->status === 'allowed';
	}

	protected function init()
	{
		parent::init();

		if ($this->isNew()) {
			$this->userId = go()->getUserId();

			if(Environment::get()->isCli()) {
				$this->ip = 'CLI';
			} else {
				$this->ip = Request::get()->getRemoteIpAddress();
			}

			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				$this->version = $_SERVER['HTTP_USER_AGENT'];

				$ua_info = \donatj\UserAgent\parse_user_agent();
				$this->platform = $ua_info['platform'] ?? '-';
				$this->name = $ua_info['browser'] ?? '-';

				$this->cutPropertiesToColumnLength();

			}else if(Environment::get()->isCli()) {
				$this->version = 'CLI';
                $this->platform = 'CLI';
                $this->name = 'CLI';
			}
		}
	}
	public static function collectGarbage(): bool
	{
		go()->debug("GC: Clients");
		$threeMonthsAgo = (new DateTime())->sub(new \DateInterval('P1M'));
		return static::internalDelete(
			(new Query)
				->tableAlias("client")
				->join("core_auth_token", "token", "token.clientId = client.id")
				->where("token.expiresAt", "!=", null)
				->where('deviceId', '=', '-') // only browsers are without deviceId
				->andWhere('lastSeen', '<', $threeMonthsAgo));
	}

}