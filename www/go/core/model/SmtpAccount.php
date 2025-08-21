<?php

namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\util\Crypt;
use go\core\validate\ErrorCode;

class SmtpAccount extends AclOwnerEntity
{
	public ?string $id;
	protected $moduleId;
	public string $hostname;
	public int $port = 587;
	public ?string $username;
	protected ?string $password;
	public ?string $encryption = "tls"; // null, 'tls' or 'ssl'
	public bool $verifyCertificate = true;
	public string $fromName;
	public string $fromEmail;

	public int $maxMessagesPerMinute = 0;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_smtp_account', 'account');
	}

	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		if(isset($log['password'])) {
			$log['password'][0] = "MASKED";
			$log['password'][1] = "MASKED";
		}

		return $log;
	}

	public function setModule($module)
	{
		$module = Module::findByName($module['package'], $module['name']);
		if (!$module) {
			$this->setValidationError('module', ErrorCode::INVALID_INPUT, 'Module was not found');
		}
		$this->moduleId = $module->id;
	}

	/**
	 * @return Filters
	 * @throws \Exception
	 */
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('module', function (Criteria $criteria, $module) {
				$module = Module::findByName($module['package'], $module['name']);
				$criteria->where(['moduleId' => $module->id]);
			});

	}
  
	public function decryptPassword(): string
	{
        return Crypt::decrypt($this->password);
	}

	/**
	 * @param string $value
	 * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
	 */
	public function setPassword(string $value)
	{
		$this->password = Crypt::encrypt($value);
	}

	protected static function textFilterColumns(): array
	{
		return ['hostname', 'fromName', 'fromEmail'];
	}

}
