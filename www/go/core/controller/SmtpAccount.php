<?php

namespace go\core\controller;

use go\core\jmap\EntityController;
use go\core\model;
use go\core\jmap\Response;

class SmtpAccount extends EntityController {

	protected function entityClass() {
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
	public function query($params) {
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
	public function get($params) {
		return $this->defaultGet($params);
	}

	/**
	 * Handles the Foo entity's Foo/set command
	 *
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 * @return array
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @throws \go\core\jmap\exception\StateMismatch
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @return array
	 * @throws \go\core\jmap\exception\InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}


	/**
	 * @param $params
	 * @throws \ReflectionException
	 */
	public function test($params) {
		
		$smtpAccount = new model\SmtpAccount();
		$smtpAccount->setValues($params);

		$message = go()->getMailer()
						->setSmtpAccount($smtpAccount)
						->compose()
						->setFrom($smtpAccount->fromEmail, $smtpAccount->fromName)
						->setTo($smtpAccount->fromEmail)
						->setSubject(go()->t('Test message'))
						->setBody(go()->t("Your settings are correct.\n\nBest regards,\n\nGroup-Office"));

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}

}
