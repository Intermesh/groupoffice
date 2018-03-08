<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

/**
 * The Dokuwiki controller
 */

namespace GO\Dokuwiki\Controller;


class Dokuwiki extends \GO\Base\Controller\AbstractController {
	
	protected function actionLoadSettings($params){
		
		$response = array();
		$response['data'] = array();
		
		$response['data']['title'] = \GO::config()->get_setting('dokuwiki_title');
		if (empty($response['data']['title'])) 
			$response['data']['title'] = 'Dokuwiki';
		
		$response['data']['external_url'] = \GO::config()->get_setting('dokuwiki_external_url');
		if (empty($response['data']['external_url'])) 
			$response['data']['external_url'] = '';
		
		$response['success'] = true;
		
		return $response;
	}
	
	protected function actionSaveSettings($params){
		
		$response = array();
		
		\GO::config()->save_setting('dokuwiki_external_url', $params['external_url']);
		\GO::config()->save_setting('dokuwiki_title', $params['title']);
		
		$response['data']['external_url'] = $params['external_url'];
		$response['data']['title'] = $params['title'];
		$response['success']=true;
		
		return $response;
	}
}

