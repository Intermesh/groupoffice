<?php

namespace go\core\model;

use GO\Base\Db\FindCriteria;
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


	public static function buildSmtpAccountFromEmailAccount(int $accountId, int $maxMessagesPerMinute): SmtpAccount
	{
		$e = \GO\Email\Model\Account::model()->find(\GO\Base\Db\FindParams::newInstance()
			->select("t.acl_id, t.id,t.username, t.password, t.smtp_host,t.smtp_port,t.smtp_encryption,t.smtp_username,t.smtp_password,t.force_smtp_login,t.smtp_allow_self_signed,a.email, a.name")
			->criteria(FindCriteria::newInstance()->addCondition('id', $accountId))
			->joinModel(array(
				'tableAlias' => 'a',
				'model' => 'GO\Email\Model\Alias',
				'foreignField' => 'account_id', //defaults to primary key of the remote model
				'type' => 'INNER',
				'criteria' => \GO\Base\Db\FindCriteria::newInstance()->addCondition('default', 1, '=', 'a')
			)))->fetch(\PDO::FETCH_OBJ);

		$a = new SmtpAccount();
		$a->hostname = $e->smtp_host;
		$a->port = $e->smtp_port;

		if($e->force_smtp_login) {
			//use imap credentials
			$a->username = $e->username;
			$a->setPassword(\GO\Base\Util\Crypt::decrypt($e->password));
		} else {
			$a->username = $e->smtp_username;
			$a->setPassword(\GO\Base\Util\Crypt::decrypt($e->smtp_password));
		}
		$a->encryption = $e->smtp_encryption;
		$a->verifyCertificate = empty($e->smtp_allow_self_signed);
		// default alias
		$a->fromName = $e->name;
		$a->fromEmail = $e->email;
		$a->maxMessagesPerMinute = $maxMessagesPerMinute;
		$a->aclId = $e->acl_id;

		return $a;
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
