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
	/**
	 * @var int
	 */
	public $id;
	protected $moduleId;
	public $hostname;
	public $port;
	public $username;
	protected $password;
	public $encryption; // null, 'tls' or 'ssl'
	public $verifyCertificate;
	public $fromName;
	public $fromEmail;

	/**
	 * @var int
	 */
	public $maxMessagesPerMinute = 0;

	/**
	 * @return Mapping
	 */
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_smtp_account', 'account');
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
