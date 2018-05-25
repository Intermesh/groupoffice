<?php


namespace GO\Calendar\Controller;


class ViewCalendarController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model we are showing and adding to the other model.
	 * 
	 * eg. When selecting calendars for a user in the sync settings this is set to \GO\Calendar\Model\Calendar
	 */
	public function modelName() {
		return 'GO\Calendar\Model\Calendar';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Calendar\Model\ViewCalendar';
	}
	
	/**
	 * The key (from the combined key) of the linkmodel that identifies the model as defined in self::modelName().
	 */
	public function linkModelField() {
		return 'calendar_id';
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm) {
		$cm->formatColumn('username', '$model->user->username');
		return parent::formatColumns($cm);
	}
}
