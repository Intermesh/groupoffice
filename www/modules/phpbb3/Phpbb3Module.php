<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Phpbb3Module.php 18900 2015-03-12 09:23:10Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

/**
 * 
 * The Dokuwiki module maintenance class
 * 
 */

namespace GO\Phpbb3;


class Phpbb3Module extends \GO\Base\Module{
	
	public function package() {
		return self::PACKAGE_UNSUPPORTED;
	}
	
	public function autoInstall() {
		return false;
	}
}
