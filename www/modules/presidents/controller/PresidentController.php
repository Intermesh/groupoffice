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
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * The President controller
 */

namespace GO\Presidents\Controller;


class PresidentController extends \GO\Base\Controller\AbstractModelController {
	
	protected $model = 'GO\Presidents\Model\President';

	/**
	 * Tell the controller to change some column values
	 */
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('party_id','$model->party->name');
		$columnModel->formatColumn('income_val','$model->income');
		return parent::formatColumns($columnModel);
	}

	/**
	 * Display corrent value in combobox
	 */
	protected function remoteComboFields(){
		return array('party_id'=>'$model->party->name');
	}
	
	protected function afterDisplay(&$response, &$model, &$params) {
		$response['data']['write_permission'] = true;
		$response['data']['permission_level'] = \GO\Base\Model\Acl::MANAGE_PERMISSION;
		$response['data']['partyName'] = $model->party->name;
		return parent::beforeDisplay($response, $model, $params);
	}
}

