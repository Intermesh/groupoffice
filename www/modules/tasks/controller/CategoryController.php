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
 * The Category controller
 *
 * @package GO.modules.Tasks
 * @version $Id: Category.php 7607 2011-09-20 10:07:50Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */


namespace GO\Tasks\Controller;


class CategoryController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Tasks\Model\Category';

	
	protected function beforeSubmit(&$response, &$model, &$params) {
		// Checkbox "Use Global" is checked
		if(isset($params['global']))
			$model->user_id = 0;
		else
			$model->user_id = \GO::user ()->id;
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name','$model->user ? $model->user->name : \GO::t("Global category", "tasks")');
		return parent::formatColumns($columnModel);
	}
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store,  \GO\Base\Db\FindParams $storeParams) {
	
		$storeParams->criteria(
			\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('user_id', \GO::user()->id)
						->addCondition('user_id', 0, '=', 't', false)
		);
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	
}

