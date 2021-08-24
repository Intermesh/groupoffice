<?php


namespace GO\Postfixadmin\Controller;

use DirectoryIterator;
use go\core\db\Query;
use go\core\fs\Folder;

class MaildirController extends \GO\Base\Controller\AbstractController {

	private $mailboxRoot = "/var/mail/vhosts/";
	private $trashRoot;
	
	public function actionCleanup($mailboxRoot = '/var/mail/vhosts/', $dryRun = 1) {
		if(!$this->isCli()) {
			echo "Try running script from the CLI \n";
			echo "Usage: php groupofficecli.php -r=postfixadmin/maildir/cleanup";
			return;
		}

		$this->mailboxRoot = rtrim($mailboxRoot, '/') . '/';
		$this->trashRoot = $this->mailboxRoot . '_trash_/';
			
		// create the trash folder
		if (!is_dir($this->trashRoot)) {
			mkdir($this->trashRoot, 0777, true);
		}

		$trashFolder = new Folder($this->trashRoot);

		$root = new Folder($this->mailboxRoot);
		
		foreach ($root->getFolders() as $domainFolder) {

			if($domainFolder->getName() == "_trash_") {
				continue;
			}

			foreach($domainFolder->getFolders() as $homeFolder) {
				$homedir = $homeFolder->getRelativePath($root) . "/";
				$existsInDatabase = (new Query())
					->selectSingleValue("id")
					->from("pa_mailboxes")
					->where(['homedir' => $homedir])
					->single();

				if($existsInDatabase) {
					echo "EXISTS: " . $homedir ."\n";
				} else {

					$mailboxTrashFolder = $trashFolder->getFolder($homedir);

					if($mailboxTrashFolder->exists()) {
						$mailboxTrashFolder = $trashFolder->getFolder($homedir . "-" . date("Y-m-d h:i:sa"));
					}

					if(!$dryRun) {
						echo "TRASH: " . $homedir ." -> " . $mailboxTrashFolder . "\n";
						$homeFolder->move($mailboxTrashFolder);
					} else {
						echo "TRASH (Dry run): " . $homedir . " -> " . $mailboxTrashFolder . "\n";
					}
				}
			}

			if($domainFolder->isEmpty()) {
				echo "TRASH: " . $domainFolder->getRelativePath($root) ."\n";
				if(!$dryRun) {
					$domainFolder->delete();
				}
			}

		}
		
	}

		/**
	 * Allow guest access
	 * 
	 * Return array with actions (in lowercase and without "action" prefix!) that 
	 * may be accessed by a guest that is not logged in.
	 * Return array('*') to allow access to all controller actions.
	 * 
	 * @return array
	 */
	protected function allowGuests(){
		return array("cleanup");
	}
}
