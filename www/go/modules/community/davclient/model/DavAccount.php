<?php
namespace go\modules\community\davclient\model;

use go\core\jmap\Entity;
use go\core\db\Criteria;
use go\core\model\Principal;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\PrincipalTrait;
use go\core\orm\Query;

/**
 * Calendar entity
 *
 */
class DavAccount extends Entity {

	const Cal = 'cal';
	const Card = 'card';

	public $id;
	/**
	 * @var string
	 * Exemple DSN: caldav:host=localhost
	 */
	public $connectionDsn;
	public $username;
	public $password;

	public $capabilities;
	public $name;
	public $lastSync;

	protected $highestItemModSeq;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_davaccount");
	}

	public function setUp() {
		return false;
	}
}
