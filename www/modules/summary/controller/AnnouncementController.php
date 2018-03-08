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


class AnnouncementController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Summary\Model\Announcement';
	
	protected function getStoreParams($params) {
		if (!empty($params['active']))
			return \GO\Base\Db\FindParams::newInstance()
				->select('t.*')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('due_time', 0, '=', 't', false)
						->addCondition('due_time', mktime(0,0,0), '>=', 't', false)
				)->order('id','DESC');
		else
			return \GO\Base\Db\FindParams::newInstance()->select('t.*');
	}
	
	protected function afterStore(&$response, &$params, &$store, $storeParams) {
		\GO\Summary\Model\LatestReadAnnouncementRecord::updateLatestRecord(\GO::user()->id);
		return parent::afterStore($response, $params, $store, $storeParams);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('user_name', '$model->user->name');
		
		return parent::formatColumns($columnModel);
	}
	
	protected function actionCheckLatestRead($params) {
		
		$response = array(
			'has_unread' => \GO\Summary\Model\LatestReadAnnouncementRecord::userHasUnreadAnnouncement(\GO::user()->id),
			'success' => true
		);
		
		echo json_encode($response);
		
	}
	
}

