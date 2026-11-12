<?php

namespace go\core\controller;

use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\core\model;
use go\core\jmap\Response;
use go\core\util\ArrayObject;
use PHPMailer\PHPMailer\Exception;

/**
 * @extends EntityController<\go\core\model\SmtpAccount>
 */
class SmtpAccount extends EntityController
{

	protected function entityClass(): string
	{
		return model\SmtpAccount::class;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @return array
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	/**
	 * Handles the Foo entity's Foo/get command
	 *
	 * @param array $params
	 * @return array
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get(array $params): ArrayObject
	{
		return $this->defaultGet($params);
	}

	/**
	 * Handles the Foo entity's Foo/set command
	 *
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 * @return \go\core\util\ArrayObject
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @throws \go\core\jmap\exception\StateMismatch
	 */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @return \go\core\util\ArrayObject
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}


	/**
	 * Send a test message with the given SMTP settings to the from address. Only for administrators.
	 *
	 * @apiParam string id ID of an existing SMTP account. The given properties override its settings.
	 * @apiParam string hostname SMTP server host name. Required when no id is given.
	 * @apiParam int port SMTP server port
	 * @apiParam ?string username User name to authenticate with
	 * @apiParam string password Password to authenticate with. When empty the saved password of the account is used.
	 * @apiParam ?string encryption null, "tls" or "ssl"
	 * @apiParam bool verifyCertificate Verify the certificate of the server
	 * @apiParam string fromName Name of the sender. Required when no id is given.
	 * @apiParam string fromEmail E-mail address of the sender and recipient. Required when no id is given.
	 * @apiParam int maxMessagesPerMinute Maximum number of messages per minute
	 * @apiResponse {success: bool} An error is returned when sending fails.
	 * @param array $params
	 * @throws Exception
	 */
	public function test(array $params)
	{
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		if (isset($params['id'])) {
			$smtpAccount = $this->getEntity($params['id']);
			// prevent 'empty' password from overriding the pre-existing
			if (empty($params['password'])) {
				unset($params['password']);
			}
		} else {
			$smtpAccount = new model\SmtpAccount();
		}

		$smtpAccount->setValues($params);

		$message = go()->getMailer()
			->setSmtpAccount($smtpAccount)
			->compose()
			->setFrom($smtpAccount->fromEmail, $smtpAccount->fromName)
			->setTo($smtpAccount->fromEmail)
			->setSubject(go()->t('Test message'))
			->setBody(go()->t("Your settings are correct.\n\nBest regards,\n\nGroup-Office"));

		$message->send();

		Response::get()->addResponse(['success' => true]);
	}

}
