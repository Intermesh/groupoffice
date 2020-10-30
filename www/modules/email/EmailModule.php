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
 * @package GO.modules.files
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Email;

use GO;
use go\core\model\User;
use go\core\Module;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use GO\Email\Model\Account;
use go\modules\community\addressbook\model\Contact;

class EmailModule extends \GO\Base\Module{	

	public static function initListeners() {

		$c = new \GO\Core\Controller\ReminderController();
		$c->addListener('reminderdisplay', "GO\Email\EmailModule", "reminderDisplay");

		// $c = new \GO\Core\Controller\AuthController();
		// $c->addListener('head', 'GO\Email\EmailModule', 'head');

		\GO\Base\Model\User::model()->addListener('delete', "GO\Email\EmailModule", "deleteUser");

		return parent::initListeners();
	}
	public function autoInstall() {
		return true;
	}
	
	public function defineListeners() {

		User::on(Property::EVENT_MAPPING, static::class, 'onMap');

		if(\go\core\model\Module::isInstalled('community', 'addressbook')) {
			Contact::on(Contact::EVENT_BEFORE_DELETE, static::class, 'onContactDelete');
		}
	}

	public static function onContactDelete(Query $query) {
		go()->getDbConnection()->delete('em_contacts_last_mail_times', (new Query)->where('contact_id', 'IN', $query))->execute();
	}

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('emailSettings', \GO\Email\Model\UserSettings::class, ['id' => 'id'], true);
	}

	// public static function head(){

	// 	$font_size = \GO::user() ? \GO::config()->get_setting('email_font_size', \GO::user()->id) : false;
	// 	if(!$font_size)
	// 		$font_size='14px';

	// 	echo "\n<!-- Inserted by EmailModule::head() -->\n<style>\n".
	// 	'.message-body,.message-body p, .message-body li, .go-html-formatted td, .em-composer .em-plaintext-body-field{'.
	// 		'font-size: '.$font_size.';!important'.
	// 	"}\n</style>\n<!-- End EmailModule::head() -->\n";
	// }


	public static function deleteUser($user) {
		Account::model()->deleteByAttribute('user_id', $user->id);
	}

	public static function reminderDisplay($controller, &$html, $params){
		if(!empty($params['unseenEmails']))
			$html .= '<p>'.str_replace('{new}', $params['unseenEmails'], GO::t("You have {new} new e-mail(s)", "email")).'</p>';
	}
}
