<?php

namespace GO\Summary\Model;

use GO\Base\Model\AbstractUserDefaultModel;
use GO\Base\Model\User;

class CalendarActiveRecord extends AbstractUserDefaultModel
{
	public function aclField()
	{
		return 'aclId';
	}

	public function tableName()
	{
		return 'calendar_calendar';
	}

	/**
	 * Creates a default model for the user.
	 *
	 * Actually nope. The default calendar is already created through JMAP
	 *
	 * @param User $user
	 * @return false
	 */
	public function getDefault(User $user, &$createdNew = false)
	{
		return false;
	}
}