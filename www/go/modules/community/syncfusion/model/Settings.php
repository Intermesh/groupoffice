<?php

namespace go\modules\community\syncfusion\model;

use go\core;

class Settings extends core\Settings
{
	/**
	 * @var string
	 */
	public $licenseKey = '';

	/**
	 * @var string
	 */
	public $documentServiceUrl = '';

	/**
	 * @var string
	 */
	public $spreadsheetServiceUrl = '';

	/**
	 * Library source: 'cdn' or 'local'
	 *
	 * @var string
	 */
	public $librarySource = 'cdn';

	/**
	 * CDN base URL including version, e.g. "https://cdn.syncfusion.com/ej2/32.1.19/"
	 *
	 * @var string
	 */
	public $cdnUrl = 'https://cdn.syncfusion.com/ej2/32.1.19/';

	/**
	 * Shared secret for JWT authorization to Docker services.
	 * When set, requests include an Authorization: Bearer <JWT> header.
	 *
	 * @var string
	 */
	public $serviceSecret = '';

	/**
	 * The settings model is serialized to every user via the Module entity
	 * (the client needs licenseKey, cdnUrl etc.). The JWT secret is only used
	 * server-side and in the admin settings form, so hide it from non-admins.
	 *
	 * @param array|null $properties
	 * @return array|null
	 */
	public function toArray(?array $properties = null): array|null
	{
		$arr = parent::toArray($properties);

		$authState = \go\core\App::get()->getAuthState();
		if (!$authState || !$authState->isAdmin()) {
			unset($arr['serviceSecret']);
		}

		return $arr;
	}
}
