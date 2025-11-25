<?php

namespace go\modules\community\email\controller;

use go\core\exception\NotFound;
use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
use go\core\jmap\exception\InvalidArguments;
use go\modules\community\email\model;


class Account extends EntityController
{

	/**
	 * The class name of the entity this controller is for.
	 *
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Account::class;
	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
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
	 * @return ArrayObject
	 * @throws \Exception
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
	 * @return ArrayObject
	 * @param array $params
	 */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}

	/**
	 * Custom API endpoint to set IMAP and/or SMTP password
	 *
	 * This endpoint enables the remote server to automatically reset the passwords for each account with the IMAP
	 * user name as set in the params, thus not forcing the administrator to log into Group-Office for each password
	 * reset.
	 * Note: you have to send a valid API key in order to do this
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments|NotFound
	 */
	public function findAndSet(array $params): ArrayObject
	{
		if(!isset($params['username'])) {
			throw new InvalidArguments('IMAP Username is required');
		}
		$accounts = model\Account::find()
			->where(['username' => $params['username']])
			->selectSingleValue('id')->all();
		if(count($accounts) < 1) {
			throw new NotFound('No IMAP accounts found in system for ' . $params['username']);
		}
		$params['force_smtp_login'] = isset($params['smtp_username']);

		// Empty SMTP login and password in case of no SMTP password enforcement
		if (!$params['force_smtp_login']) {
			$params['smtp_username'] = '';
			$params['smtp_password'] = '';
		}

		$arUpdates = [];
		foreach($accounts as $account) {
			$arUpdates[$account] = $params;
		}
		return $this->set(['update' => $arUpdates]);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}
}
