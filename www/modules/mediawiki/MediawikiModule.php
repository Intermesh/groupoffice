<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MediawikiModule.php 21640 2017-11-07 11:25:37Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */

namespace GO\Mediawiki;

use GO;
use GO\Base\Model\Acl;
use GO\Base\Model\User;
use GO\Base\Module;
use GO\Notes\Model\Category;

/**
 * 
 * The Notes module maintenance class
 * 
 */
class MediawikiModule extends Module{
	
	public function package() {
		return self::PACKAGE_UNSUPPORTED;
	}
	
	public function autoInstall() {
		return false;
	}
}
