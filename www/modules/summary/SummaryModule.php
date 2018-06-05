<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * This class is used to parse and write RFC822 compliant recipient lists
 * 
 * @package GO.modules.summary
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Summary;


class SummaryModule extends \GO\Base\Module{	

	
	public function autoInstall() {
		return true;
	}
	
}
