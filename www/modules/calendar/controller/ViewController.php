<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * The View controller
 *
 * @package GO.modules.Calendar
 * @version $Id: View.php 7607 2012-04-12 13:37:41Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */


namespace GO\Calendar\Controller;


class ViewController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Calendar\Model\View';
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name','$model->user->name',array(),'user_id');
		return parent::formatColumns($columnModel);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name','ASC');
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		$url = \GO::createExternalUrl('calendar', 'openCalendar', array(	
			'view_id'=>$response['data']['id']
		));
		
		$response['data']['url']='<a class="normal-link" target="_blank" href="'.$url.'">'.\GO::t("Right click to copy link location", "calendar").'</a>';
		
		return parent::afterLoad($response, $model, $params);
	}
	
}
