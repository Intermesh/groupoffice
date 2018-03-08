<?php

namespace GO\Chat;

use Exception;
use GO;
use GO\Base\Db\FindParams;
use XmppPrebind;

class ChatModule extends \GO\Base\Module {
	
	public function package() {
		return self::PACKAGE_UNSUPPORTED;
	}

	public static function initListeners() {


		$c = new \GO\Core\Controller\AuthController();
		$c->addListener('headstart', 'GO\Chat\ChatModule', 'headstart');

		GO::session()->addListener('login', 'GO\Chat\ChatModule', 'login');

		parent::initListeners();
	}

	public static function headstart() {



		if (GO::modules()->chat) {
			//regenerate Prosody groups file
			$aclMtime = GO::config()->get_setting('chat_acl_mtime');
			if ($aclMtime != GO::modules()->chat->acl->mtime || !self::getGroupsFile()->exists()) {
				self::generateGroupsFile();

				GO::config()->save_setting('chat_acl_mtime', GO::modules()->chat->acl->mtime);
			}



//			$url = GO::config()->host . 'modules/chat/converse.js-0.8.6/';
			$url = GO::config()->host . 'modules/chat/converse.js-3.0.0/';
			$head = '
<link rel="stylesheet" type="text/css" media="screen" href="https://cdn.conversejs.org/3.0.0/css/converse.min.css">
<script src="https://cdn.conversejs.org/3.0.0/dist/converse.min.js"></script>

			<!-- <link rel="stylesheet" type="text/css" media="screen" href="' . $url . 'css/converse.min.css">
			<script src="' . $url . 'dist/converse.min.js"></script> -->
			
			';
/*
 * 
 * <script src="' . $url . 'dist/converse.min.css"></script>
			<script src="' . $url . 'src/converse.js"></script>
 * 
 * 
 */
			echo $head;
		}
	}

	public static function login($username, $password, $user, $countLogin) {
		if (GO::modules()->chat && $countLogin && isset($_SERVER['HTTP_HOST'])) {
			
			$enc = \GO\Base\Util\Crypt::encrypt($password);
			if(!$enc){
				trigger_error("Chat password encryption failed!", E_USER_NOTICE);
			}
			GO::session()->values['chat']['p'] = $enc;
		}
	}

	/**
	 * Can only be fetched once. The XMPP Session is not valid anymore on refresh. User will have to relogin manually.
	 * @return array
	 */
	public static function getPrebindInfo() {


		require GO::config()->root_path . 'modules/chat/xmpp-prebind-php/lib/XmppPrebind.php';

		GO::debug("CHAT: Pre binding to XMPP HOST: " . self::getXmppHost() . " BOSH URI: " . self::getBoshUri() . " with user " . GO::user()->username);

		if (isset(GO::session()->values['chat']['p'])) {

			try {


				$xmppPrebind = new XmppPrebind(
								self::getXmppHost(), self::getBoshUri(), GO::config()->product_name, strpos(self::getBoshUri(), 'https') !== false, false);

				if ($xmppPrebind->connect(GO::user()->username, \GO\Base\Util\Crypt::decrypt(GO::session()->values['chat']['p']))) {

					$xmppPrebind->auth();

					GO::debug("CHAT: connect successfull");
					// array containing sid, rid and jid			
					$ret = $xmppPrebind->getSessionInfo();
					$ret['prebind'] = "true";

					return $ret;
				} else {
					GO::debug("CHAT: failed to connect");
				}
			} catch (Exception $e) {
				GO::debug("CHAT: Authentication failed: " . $e);
			}
		}else
		{
			GO::debug("CHAT: Password not set");
		}



		$ret = array(
				'prebind' => 'false',
				'jid' => '',
				'sid' => '',
				'rid' => ''
		);


		return $ret;
	}

	public static function getBoshUri() {
//		$jabberHost = 'intermesh.group-office.com';
//		$boshUri = 'https://' . $jabberHost . ':5281/http-bind';

		$proto = GO::request()->isHttps() ? 'https' : 'http';

		$port = GO::request()->isHttps() ? 5281 : 5280;

		return isset(GO::config()->chat_bosh_uri) ? GO::config()->chat_bosh_uri : $proto . '://' . self::getXmppHost() . ':' . $port . '/http-bind';
	}

	public static function getXmppHost() {
		//$jabberHost = 'intermesh.group-office.com';
		return isset(GO::config()->chat_xmpp_host) ? GO::config()->chat_xmpp_host : $_SERVER['HTTP_HOST'];
	}

	/**
	 * Get the file with groups info for Prosody
	 * 
	 * @return \GO\Base\Fs\File
	 */
	public static function getGroupsFile() {
		$folder = new GO\Base\Fs\Folder(GO::config()->file_storage_path . 'chat');

		$folder->create();

		$file = $folder->createChild('groups.txt');

		return $file;
	}

	public static function generateGroupsFile() {

		$showGroups = GO::config()->show_groups_in_chat;

		$file = self::getGroupsFile();

		$fp = fopen($file->path(), 'w');
		$xmppHost = self::getXmppHost();

		if(!$showGroups) {
			fwrite($fp, "[" . GO::config()->product_name . " " . strtolower(GO::t("Users")) . "]\n");

			\GO\Base\Model\Acl::getAuthorizedUsers(GO::modules()->chat->acl_id, \GO\Base\Model\Acl::READ_PERMISSION, function($user) use ($fp, $xmppHost) {
				if($user->enabled){
					$line = $user->username . '@' . $xmppHost . '=' . $user->name . "\n";
					fwrite($fp, $line);
				}
			});
			fclose($fp);
			return;
		}
		
		$isRenderedUserList = array();
		
		$groupStmt = GO\Base\Model\Group::model()->find(FindParams::newInstance());
		
		foreach ($groupStmt as $groupModel) {
			
			foreach ($groupModel->users as $userModel) {
		
				if($userModel->enabled && \GO\Base\Model\Acl::getUserPermissionLevel(GO::modules()->chat->acl_id, $userModel->id)) {
					$groupName = $groupModel->name;
					if(!isset($$groupName)) {
						fwrite($fp, "\n[" . $groupModel->name . " " . strtolower(GO::t("group")) . "]\n"); 
						$$groupName = true;
					}
					
					$isRenderedUserList[$userModel->id] = $userModel->id;
					$line = $userModel->username . '@' . $xmppHost . '=' . $userModel->name . "\n";
					fwrite($fp, $line);
				}
				
			}
		}
		
		$userStmt = GO\Base\Model\User::model()->find(FindParams::newInstance()->criteria(GO\Base\Db\FindCriteria::newInstance()->addInCondition('id', $isRenderedUserList, 't', true, true)));
		
		foreach ($userStmt as $userModel) {
			if($userModel->enabled && \GO\Base\Model\Acl::getUserPermissionLevel(GO::modules()->chat->acl_id, $userModel->id)) {
					if(!isset($rendereOthersLable)) {
						fwrite($fp, "\n[" . strtolower(GO::t("others")) . " " . strtolower(GO::t("group")) . "]\n"); 
						$RendereOthersLable = true;
					}
					$line = $userModel->username . '@' . $xmppHost . '=' . $userModel->name . "\n";
					fwrite($fp, $line);
			}
		}
		
		fclose($fp);
	}

}
