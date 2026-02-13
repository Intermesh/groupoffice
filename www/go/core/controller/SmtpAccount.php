<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;
use go\core\jmap\Response;
use go\core\util\ArrayObject;
use PHPMailer\PHPMailer\Exception;

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
	 * @param array $params
	 * @throws Exception
	 */
	public function test(array $params)
	{
		if (isset($params['id'])) {
			$smtpAccount = model\SmtpAccount::findById($params['id']);
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
