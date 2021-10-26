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

use GO\Base\Exception\Validation;

class RssFeedController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Summary\Model\RssFeed';

	protected function actionSaveFeeds($params) {

		$feeds = json_decode($params['feeds'], true);
		$ids = array();

		$response['data'] = array();
		foreach ($feeds as $feed) {
			//$feed['user_id'] = \GO::user()->id;
			
			if(!empty($feed['id']))
				$feedModel = \GO\Summary\Model\RssFeed::model()->findByPk($feed['id']);
			else
				$feedModel = new \GO\Summary\Model\RssFeed();
			
			$feedModel->setAttributes($feed);
			if(!$feedModel->save()) {
				throw new Validation($feedModel->getValidationError('url'));
			}
			$feed['id'] = $feedModel->id;

			$ids[] = $feed['id'];
			$response['data'][$feed['id']] = $feed;
		}

		// delete other feeds
		$feedStmt = \GO\Summary\Model\RssFeed::model()
						->find(
						\GO\Base\Db\FindParams::newInstance()
						->criteria(
										\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('user_id', \GO::user()->id)
										->addInCondition('id', $ids, 't', true, true)
						)
		);
		while ($deleteFeedModel = $feedStmt->fetch())
			$deleteFeedModel->delete();

		$response['ids'] = $ids;
		$response['success'] = true;

		return $response;
	}

	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		$storeParams->getCriteria()->addCondition('user_id', \GO::user()->id);
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected function getStoreParams($params) {
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('user_id', \GO::user()->id);
		return \GO\Base\Db\FindParams::newInstance()
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

				if(!preg_match('/<rss.*<\/rss>/i', str_replace(["\r","\n"],'', $xml))) {
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

}

