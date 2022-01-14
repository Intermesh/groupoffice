<?php

namespace go\modules\community\oauth2client\model;

use go\core\orm\Entity;

final class DefaultClient extends Entity
{
	/** @var int */
	public $id;

	/**
	 * @var string
	 */
	public $authenticationMethod;

	/**
	 * @var string
	 */
	public $imapHost;

	/**
	 * @var int
	 */
	public $imapPort;

	/**
	 * @var string
	 */
	public $imapEncryption;

	/**
	 * @var string
	 */
	public $smtpHost;

	/**
	 * @var int
	 */
	public $smtpPort;

	/**
	 * @var string
	 */
	public $smtpEncryption;


	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("oauth2client_default_client", "defaultclient");
	}

}