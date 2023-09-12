<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

namespace GO\Summary\Controller;

use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\Validation;
use go\core\model\User;
use GO\Summary\Model\RssFeed;

class RssFeedController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Summary\Model\RssFeed';

	protected function actionSaveFeeds(array $params)
	{
		$feeds = json_decode($params['feeds'], true);
		$ids = array();
		$currUserId = \GO::User()->id;
		$stmt = User::find(['id'])->where(['enabled' => true])->all();
		foreach($stmt as $u) {
			$allUsers[] = $u->id;
		}

		$response['data'] = array();
		foreach ($feeds as $feed) {
			if(!empty($feed['id'])) {
				$feedModel = RssFeed::model()->findByPk($feed['id']);
				$this->doSave($feedModel, $feed, $currUserId);
				$ids[] = $feed['id'];
				$response['data'][$feed['id']] = $feed;
			} else {
				$ar = [$currUserId];
				if(isset($feed['allUsers'])) {
					$ar = $allUsers;
				}
				foreach($ar as $userId) {
					$feedModel = new RssFeed();
					$this->doSave($feedModel, $feed, $userId);
//					$feed['id'] = $feedModel->id;
					$ids[] = $feedModel->id;
					$response['data'][$feedModel->id] = $feed;
				}
			}
		}

		// delete other feeds
		$feedStmt = RssFeed::model()
						->find(
						FindParams::newInstance()
						->criteria(
										FindCriteria::newInstance()
										->addCondition('user_id', $currUserId)
										->addInCondition('id', $ids, 't', true, true)
						)
		);
		while ($deleteFeedModel = $feedStmt->fetch()) {
			$deleteFeedModel->delete();
		}

		$response['ids'] = $ids;
		$response['success'] = true;

		return $response;
	}

	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, FindParams $storeParams) {
		$storeParams->getCriteria()->addCondition('user_id', \GO::user()->id);
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected function getStoreParams($params) {
		$findCriteria = FindCriteria::newInstance()
						->addCondition('user_id', \GO::user()->id);
		return FindParams::newInstance()
						->select('t.*')
						->criteria($findCriteria);
	}

	
	protected function actionProxy($params) {
		$feed = $params['feed'];
		if ($feed != '' && strpos($feed, 'http') === 0) {
			header('Content-Type: text/xml');

			if (function_exists('curl_init')) {				
				$httpclient = new \GO\Base\Util\HttpClient();
				$xml = $httpclient->request($feed);
			} else {
				if (!\GO\Base\Fs\File::checkPathInput($feed))
					throw new \Exception("Invalid request");

				$xml = @file_get_contents($feed);
			}

			if ($xml) {

				if(!RssFeed::isRSS($xml)) {
					throw new \Exception("No RSS feed");
				}

				//fix relative images
				preg_match('/(.*:\/\/[^\/]+)\//',$feed, $matches);				
				$baseUrl = $matches[1];				
				$xml = str_replace('src=&quot;/', 'src=&quot;'.$baseUrl.'/', $xml);
				$xml = str_replace('src="/', 'src=&quot;'.$baseUrl.'/', $xml);
				
				$xml = str_replace('href=&quot;/', 'href=&quot;'.$baseUrl.'/', $xml);
				$xml = str_replace('href="/', 'href="'.$baseUrl.'/', $xml);
				
				$xml = str_replace('<content:encoded>', '<content>', $xml);
				$xml = str_replace('</content:encoded>', '</content>', $xml);
				$xml = str_replace('</dc:creator>', '</author>', $xml);
				echo str_replace('<dc:creator', '<author', $xml);
			}
		}
	}

	/**
	 *
	 * @param RssFeed $feedModel
	 * @param array $feed
	 * @param int $userId
	 *
	 * @throws Validation
	 * @throws AccessDenied
	 */
	private function doSave(RssFeed &$feedModel, array $feed, int $userId)
	{
		$feedModel->setAttributes($feed);
		$feedModel->user_id = $userId;
		if(!$feedModel->save()) {
			throw new Validation($feedModel->getValidationError('url'));
		}

	}

}

