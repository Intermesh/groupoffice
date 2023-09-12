<?php

namespace go\core\model;

use go\core\Environment;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\util\DateTime;

class Client extends Property
{
	public $id;
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

	protected static function internalDelete(Query $query): bool
	{
		$query->select('id')->setModel(self::class)->from('core_client', 'cl'); // needed for proerty
		if(!Token::delete(['clientId' => $query])) {
			throw new \Exception("Could not delete token");
		}
		return parent::internalDelete($query);
	}

	protected function init()
	{
		parent::init();

		if ($this->isNew()) {
			$this->userId = go()->getUserId();

			if (isset($_SERVER['REMOTE_ADDR'])) {
				$this->ip = $_SERVER['REMOTE_ADDR'];
			} else if(Environment::get()->isCli()) {
				$this->ip = 'CLI';
			}

			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				$this->version = $_SERVER['HTTP_USER_AGENT'];

				$ua_info = \donatj\UserAgent\parse_user_agent();
				$this->platform = $ua_info['platform'] ?? '-';
				$this->name = $ua_info['browser'] ?? '-';

			}else if(Environment::get()->isCli()) {
				$this->version = 'CLI';
                $this->platform = 'CLI';
                $this->name = 'CLI';
			}
		}
	}
	public static function collectGarbage(): bool
	{
		$threeMonthsAgo = (new DateTime())->sub(new DateInterval('P3M'));
		return static::delete(
			(new Query)
				->andWhere('lastSeen', '<', $threeMonthsAgo));
	}

}