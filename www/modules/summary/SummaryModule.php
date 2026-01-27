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


use GO\Summary\Model\Announcement;

class SummaryModule extends \GO\Base\Module{

	
	public function autoInstall() {
		return true;
	}

	/**
	 * Default sort order when installing. If null it will be auto generated.
	 * @return int|null
	 */
	public static function getDefaultSortOrder() : ?int{
		return 5;
	}

	public function install()
	{
		if(!parent::install()) {
			return false;
		}

		$a = new Announcement();
		$a->title = go()->t("welcomeTitle", "legacy", "summary");
		$a->content = str_replace("{{link}}", "<a target=\"_blank\" href=\"https://groupoffice.readthedocs.io/en/latest/getting-started.html\">https://groupoffice.readthedocs.io/en/latest/getting-started.html</a>", go()->t("welcomeContent", "legacy", "summary"));
		$a->save();

		$a->getAcl()->addGroup(\GO::config()->group_everyone, \GO\Base\Model\Acl::READ_PERMISSION);


		return true;
	}

}
