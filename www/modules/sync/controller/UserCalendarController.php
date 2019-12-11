<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */


namespace GO\Sync\Controller;


class UserCalendarController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String 
	 */
	public function modelName() {
		return 'GO\Calendar\Model\Calendar';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Sync\Model\UserCalendar';
	}
	
	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'calendar_id';
	}	
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm) {
		$cm->formatColumn('default_calendar', 'isset($model->default_calendar) ? intval($model->default_calendar) : 0');
		$cm->formatColumn('permission_level', '$model->permissionLevel');
		return parent::formatColumns($cm);
	}

	protected function actionSelectedStore($params)
  {
    $user = \GO\Base\Model\User::model()->findByPk($params['model_id']);
    \GO\Sync\Model\Settings::model()->findForUser($user);

    return parent::actionSelectedStore($params);
  }
}
