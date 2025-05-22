<?php
namespace go\modules\community\email\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;
use go\core\util\Crypt;

class EmailAccount extends AclOwnerEntity {
	
	public $id;

	public $name;
	public $email;
	public $quota;

	/**
	 * Data Source Name
	 *
	 * supported params (imap|smtp):
	 * - host
	 * - user
	 * - pass
	 * - encryption
	 * - novalidate
	 *
	 * Example
	 * imap:host=localhost;port=993;encryption=ssl
	 * @var string
	 */
	protected $mtaDsn; // smtp
	protected $mdaDsn; // imap
	protected $mdaCapabilities;

	public $createdAt;
	public $modifiedAt;

	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("email_account");
	}

	public function getCapabilities() {
		return [
			'maxMailboxesPerEMail'=>1,
			'maxMailboxDepth' => 10,
			'maxSizeMailboxName' => 255,
			'maxSizeAttachmentsPerEMail'=>25000000,
			'emailQuerySortOptions'=> '{}',
			'mayCreateTopLevelMailbox'=>true
		];
	}

	public function getMyRights() {
		$lvl = $this->getPermissionLevel();
		return [
			'mayReadItems' => $lvl >= 10,
			'mayReadIdentities' => $lvl >= 20, // use identity to send mail
			'mayFlagItems' => $lvl >= 25, // mark read, flag, (forward and answered = read identities)
			'mayWriteItems' => $lvl >= 30, // move delete, flag
			'mayWrite' => $lvl >= 40, // change
			'mayAdmin' => $lvl >= 50, // set permission
		];
	}

	private function readDsn($dsn) {
		list($type, $params) = explode(':', $dsn,2);
		$prop = strtok($params, ";");
		$data = [];

		while ($prop !== false) {
			list($key, $value) = explode('=', $prop, 2);
			$data[$key] = $value;
			$prop = strtok(";");
		}
		return [$type,(object)$data];
	}

	private function writeDsn($arr) {
		return array_reduce(
			array_keys($arr),
			fn ($carry, $key) => $carry.';'.$key.'='.str_replace([';','='],['',''],$arr[$key]),
			''
		);
	}

	public function getMta() {
		list($type, $data) = $this->readDsn($this->mtaDsn);
		if($type !== 'smtp') {
			throw new \Exception('Unsupported MTA type');
		}
		unset($data->pass);
		return $data;
	}

	public function setMda($value) {
		if(!empty($this->mdaDsn) && !isset($value['pass'])) {
			list(,$old) = $this->readDsn($this->mdaDsn);
			$value['pass'] = $old->pass;
		} else if($value['pass']) {
			$value['pass'] = Crypt::encrypt($value['pass']);
		}
		$this->mdaDsn = 'imap:'.$this->writeDsn($value);
	}

	public function setMta($value) {
		if(!empty($this->mtaDsn) && !isset($value['pass'])) {
			list(,$old) = $this->readDsn($this->mtaDsn);
			$value['pass'] = $old->pass;
		} else if($value['pass']) {
			$value['pass'] = Crypt::encrypt($value['pass']);
		}
		$this->mtaDsn = 'smtp:'.$this->writeDsn($value);
	}

	public function getMda() {
		list($type, $data) = $this->readDsn($this->mdaDsn);
		if($type !== 'imap') {
			throw new \Exception('Unsupported MDA type');
		}
		unset($data->pass);
		return $data;
	}

	public function mdaCapabilities() {
		return json_decode($this->mdaCapabilities);
	}

	protected function internalValidate()
	{
		if($this->isModified('mdaDsn')) {
			// test connection and fetch capabilities
			$conn = $this->connect();
			$capa = $conn->imap->capability();
			$this->mdaCapabilities = json_encode($capa ?? []);
		}

		parent::internalValidate();
	}

	private $backend;
	public function backend() {
		if(!isset($this->backend))
			$this->backend = $this->connect();
		return $this->backend;
	}

	public function connect() {
		// only imap backend for now.
		list($type, $data) = $this->readDsn($this->mdaDsn);
		if($type !== 'imap') {
			throw new \Exception('Unsupported MDA type');
		}

		$data->pass = Crypt::decrypt($data->pass);
		try {
			return ImapBackend::connect($data, $this);
		} catch(\ErrorException $e) {
			throw new \Exception('No connection to IMAP host '.$data->host);
		}
	}
}
