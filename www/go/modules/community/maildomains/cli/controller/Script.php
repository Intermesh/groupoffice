<?php

namespace go\modules\community\maildomains\cli\controller;

use go\core\Controller;
use go\core\db\Query;
use go\core\fs\Folder;

final class Script extends Controller
{
	private $mailboxRoot = "/var/mail/vhosts/";
	private $trashRoot;

	/**
	 * Clean up removed mail boxes
	 *
	 * ./cli.php community/maildomains/Script/cleanup  --dryRun=[0|1]
	 */
	public function cleanup(array $params)
	{
		extract($this->checkParams($params, ['dryRun' => 1, "mailboxRoot" => "/var/mail/vhosts"]));

		$this->mailboxRoot = rtrim($mailboxRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->trashRoot = $this->mailboxRoot . '_trash_' . DIRECTORY_SEPARATOR;

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
					->from("community_maildomains_mailbox")
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
}