<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * This handels notification messages set for user feedback
 *
 * @package GO.modules.sites.components
 * @copyright Copyright Intermesh
 * @version $Id Notifier.php 2012-06-29 16:13:40 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Components;


class Notifier
{
	/**
	 * checks if there is a notification message with the given value
	 * @param StringHelper $key name of the note
	 * @return boolean if the note excists
	 */
	public function hasMessage($key)
	{
			return $this->getMessage($key, false)!==null;
	}
	
	/**
	 * Get the notification message and delete it afterwards
	 * @param StringHelper $key the key of the notification
	 * @param boolean $remove should the note be remove from session after getting it
	 * @return null 
	 */
	public function getMessage($key,$remove=true)
	{
		if(!isset(\GO::session()->values['notifier'][$key]))
			return null;
		else
			$value = \GO::session()->values['notifier'][$key];
			
		if($remove)
			unset(\GO::session()->values['notifier'][$key]);

		return $value;
		
	}
	
	/**
	 * add a new note to the session
	 * @param StringHelper $key the key of the message
	 * @param StringHelper $value the message
	 */
	public function setMessage($key,$value)
	{
		\GO::session()->values['notifier'][$key] = $value;	
	}
}
?>
