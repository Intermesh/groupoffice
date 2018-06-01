<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Thrown when a user doesn't have access
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: OtherLoginLocation.php 6002 2010-10-27 13:21:25Z mschering $
 * @copyright Copyright Intermesh
 * @package GO.base.exception
 * 
 * @uses Exception
 */


namespace GO\Base\Exception;


class OtherLoginLocation extends \Exception
{

	public function __construct($message='') {
		
		$message = empty($message) ? \GO::t("Already logged in on other location") : \GO::t("Already logged in on other location")."\n\n".$message;
		$code = 498;		
		parent::__construct($message,$code);
	}
}
