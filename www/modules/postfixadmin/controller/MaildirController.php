<?php


namespace GO\Postfixadmin\Controller;

use DirectoryIterator;
use go\core\db\Query;

class MaildirController extends \GO\Base\Controller\AbstractController {

	private $mailboxRoot = "/var/mail/vhosts/";
	private $trashRoot = "/var/trash/";
	
	private function isCommandLineInterface()
	{
		return (php_sapi_name() === 'cli');
	}

	public function actionCleanup() {
		if($this->isCommandLineInterface()) {
			
			// create the trash folder
			if (!is_dir($this->trashRoot)) {
				mkdir($this->trashRoot, 0777, true);
			}

			$dirs = new DirectoryIterator($this->mailboxRoot);
			
			foreach ($dirs as $dir) {
				if(!$dir->isDot() && $dir->isDir()) {
					$dirName = $dir->getFileName();

					if(!is_dir($this->trashRoot . $dirName)) {
						mkdir($this->trashRoot . $dirName, 0777, true);
					}

				$subDirs = new DirectoryIterator($this->mailboxRoot . $dirName);
					foreach($subDirs as $subdir) {
						if(!$subdir->isDot() && $subdir->isDir()) {
							$subDirName = $subdir->getFilename();
							$homeDir = $dirName . "/" . $subDirName . "/";
							$result = (new Query())
							->selectSingleValue("id")							
							->from("pa_mailboxes")
							->where(['homedir' => $homeDir])
							->single();

							// mailbox was found on file system but not in database.
							if(!$result) {
								$mailDirs = new DirectoryIterator($this->mailboxRoot . $homeDir);
								foreach($mailDirs as $mailDir) {
									if(!$mailDir->isDot() && $mailDir->isDir()) {
										if($mailDir == "Maildir" || $mailDir == "cur") {
											echo "Maildir found in : " . $subDirName . "\n";
											echo "Moving directory \n";

											if(!is_dir($this->trashRoot . "/" . $homeDir)) {
												rename($this->mailboxRoot . $homeDir, $this->trashRoot . "/" . $homeDir);
											} else {
												$dateAndTime = date("Y-m-d h:i:sa");
												echo $this->trashRoot . "/" . $homeDir . $dateAndTime;
												rename($this->mailboxRoot . $homeDir, $this->trashRoot . "/" . $dirName . "/" . $subDirName . $dateAndTime);
											}
											
										}
									}
								}
							} 
						}
					}
				}
			}
		} else {
			echo "Try running script from the CLI \n";
			echo "Usage: php groupofficecli.php -r=postfixadmin/maildir/cleanup";
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
