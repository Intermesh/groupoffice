<?php
namespace go\modules\community\davclient\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;
use go\modules\community\calendar\model\ICalendarHelper;

/**
 * Calendar entity
 *
 */
class DavAccount extends AclOwnerEntity {

	const Cal = 'cal';
	const Card = 'card';

	public $id;
	public $active;
	public $host;
	public $username;
	public $password;
	public $basePath;
	public $principalUri;
	public $capabilities;
	public $name;
	public $refreshInterval;
	public $lastSync;
	public $lastError;
	/** @var Calendar[] the collections items with ctag and uri */
	public $collections = [];

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_davaccount", 'a')
			->addMap('collections', Calendar::class, ['id' => 'davaccountId']);
	}

	public function synchronizer() {
		$syncer = new DavSynchronizer($this);
		if(!$this->isSetup()) {
			$r = $syncer->serviceDiscovery();
			//$this->host = $r['baseUri'];
			$this->principalUri = $r['principalHref'];
			foreach($r['collections'] as $data) {
				$cal = $this->addCalendar($data);
				if(!empty($cal)) {
					$syncer->fetchEvents($cal);
				}

			}
			$this->lastSync = new \DateTime();
			$this->save();
		}
		return $syncer;
	}

	public static function findByCalendarId($id) {
		return self::find()->join('davclient_calendar', 'c', 'c.accountId = a.id')
			->where('c.calendarId', '=', $id)->single();
	}

	public function put($event) {
		$syncer = new DavSynchronizer($this);
		return $syncer->put($event);
	}

	private function randomColor($seed) {
		srand(crc32($seed));
		$nb = rand(0,17);
		return substr('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1',
			($nb*7)+1,6);
	}

	private function addCalendar($data)
	{
		if($this->collections == null) {
			$this->collections = [];
		}
		if(array_key_exists($data['uri'], $this->collections))
			$cal = $this->collections[$data['uri']];
		if (empty($cal)) {
			$model = new \go\modules\community\calendar\model\Calendar();
			$model->name = $data['name'];
			$model->description = $data['description'];
			$model->sortOrder = is_numeric($data['sortOrder']) ? (int)$data['sortOrder'] : 1;
			$model->color = !empty($data['color']) ? $data['color'] : $this->randomColor($data['name']);
			$model->timeZone = $data['timeZone'];
			if ($model->save()) {
				$cal = new Calendar($this);
				$cal->uri = $data['uri'];
				$cal->ctag = $data['ctag'];
				$cal->addModel($model);
				$this->collections[$cal->uri] = $cal;
			} else {
				go()->log('Could not save Calendar '.print_r($model->getValidationErrors(),true));
			}
		}
		return $cal;
	}

	private function isSetup() {
		return !empty($this->lastSync);
	}

}
