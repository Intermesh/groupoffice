<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @property int $user_id
 * @property string $title
 * @property string $url
 * @property boolean $summary
 */


namespace GO\Summary\Model;


use go\core\http\Client;
use go\core\model\Module;

class RssFeed extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName(): string
	{
		return 'su_rss_feeds';
	}
	
	protected function init(): void
	{
		$this->columns['url']['gotype']='html';
		parent::init();
	}

	public function validate(): bool
	{
		$client = new Client();
		$client->globalRangeOnly = true;
		$client->maxResponseSize = 10 * 1024 * 1024;
		$response = $client->get($this->url);

		if (!$response['body'] || !self::isRSS($response['body'])) {

			$this->setValidationError('url', go()->t('The supplied URL is not an RSS feed'));
		}

		return parent::validate();
	}

	/**
	 * A feed belongs to a user. Only the owner and administrators may read, change or delete it. Managers of the
	 * module may also create feeds for other users with the "All users" option.
	 *
	 * @param int $level
	 * @return bool
	 */
	public function checkPermissionLevel($level)
	{
		if (\GO::$ignoreAclPermissions) {
			return true;
		}

		$userId = go()->getAuthState()->getUserId();
		if (!$userId) {
			return false;
		}

		if (go()->getAuthState()->isAdmin() || $this->user_id == $userId) {
			return true;
		}

		return $this->isNew() && self::mayManage();
	}

	/**
	 * Check if the current user may manage the summary module and so add feeds for all users
	 *
	 * @return bool
	 */
	public static function mayManage(): bool
	{
		$module = Module::findByName(null, 'summary');
		return $module && !empty($module->getUserRights()->mayManage);
	}

	public static function isRSS($string): bool
	{
		return preg_match('/<rss.*<\/rss>/i', str_replace(["\r","\n"],'', $string)) ||
		preg_match('/<rdf:RDF.*<\/rdf:RDF>/i', str_replace(["\r","\n"],'', $string));
	}

}
