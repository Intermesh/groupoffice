<?php


namespace GO\Calendar\Controller;


class GroupAdminController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model we are showing and adding to the other model.
	 * 
	 * eg. When selecting calendars for a user in the sync settings this is set to \GO\Calendar\Model\Calendar
	 */
	public function modelName() {
		return 'GO\Base\Model\User';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Calendar\Model\GroupAdmin';
	}
	
	/**
	 * The key (from the combined key) of the linkmodel that identifies the model as defined in self::modelName().
	 */
	public function linkModelField() {
		return 'user_id';
	}

}
