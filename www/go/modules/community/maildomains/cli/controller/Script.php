<?php

namespace go\modules\community\maildomains\cli\controller;

use go\core\Controller;
use go\core\db\Query;
use go\core\Environment;
use go\core\exception\NotFound;
use go\core\fs\Folder;
use go\modules\community\maildomains\install\Migrator;
use go\modules\community\maildomains\model;

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

			if ($domainFolder->getName() == "_trash_") {
				continue;
			}

			foreach ($domainFolder->getFolders() as $homeFolder) {
				$homedir = $homeFolder->getRelativePath($root) . "/";
				$existsInDatabase = (new Query())
					->selectSingleValue("id")
					->from("community_maildomains_mailbox")
					->where(['homedir' => $homedir])
					->single();

				if ($existsInDatabase) {
					echo "EXISTS: " . $homedir . "\n";
				} else {

					$mailboxTrashFolder = $trashFolder->getFolder($homedir);

					if ($mailboxTrashFolder->exists()) {
						$mailboxTrashFolder = $trashFolder->getFolder($homedir . "-" . date("Y-m-d h:i:sa"));
					}

					if (!$dryRun) {
						echo "TRASH: " . $homedir . " -> " . $mailboxTrashFolder . "\n";
						$homeFolder->move($mailboxTrashFolder);
					} else {
						echo "TRASH (Dry run): " . $homedir . " -> " . $mailboxTrashFolder . "\n";
					}
				}
			}

			if ($domainFolder->isEmpty()) {
				echo "TRASH: " . $domainFolder->getRelativePath($root) . "\n";
				if (!$dryRun) {
					$domainFolder->delete();
				}
			}

		}


	}

	/**
	 * Migrates DKIM keys from filesystem to database.
	 * It can't run automatically as www-data has no access to the files. This must be changed first.
	 *
	 * chown www-data -R /var/lib/rspamd/dkim
	 *
	 * docker compose exec -u www-data groupoffice-develop ./www/cli.php community/maildomains/Script/migrateDKIM
	 *
	 * @param array $params
	 * @return void
	 */
	public function migrateDKIM(array $params) {
			$m = new Migrator();
			$m->migrateDKIM($params['folder'] ?? "/var/lib/rspamd/dkim", $params['selector'] ?? "mail");
	}

	/**
	 * Check if a domain or local part can safely be used as a single folder name
	 *
	 * @param string $segment
	 * @return bool
	 */
	private function isSafePathSegment(string $segment): bool {
		return $segment !== '' && $segment !== '.' && $segment !== '..' && strpbrk($segment, "/\\\0") === false;
	}

	/**
	 * Move the contents of a mailbox home directory into a "Maildir" folder inside it.
	 *
	 * The new home directory gets the same owner, group and mode as the old one.
	 *
	 * @param string $home Home directory with trailing slash
	 * @param string $maildir New Maildir with trailing slash
	 * @param string $tmpPath Temporary path on the same file system to move the home directory to
	 * @param string[] $homeFiles Glob patterns of files to move back to the home directory
	 * @throws \Exception
	 */
	private function moveToMaildir(string $home, string $maildir, string $tmpPath, array $homeFiles): void {
		$homeDir = rtrim($home, '/');
		$stat = stat($homeDir);
		if($stat === false) {
			throw new \Exception("Could not stat " . $homeDir);
		}

		echo "Moving " . $homeDir . " to " . $tmpPath . "\n";
		if(!rename($homeDir, $tmpPath)) {
			throw new \Exception("Could not move " . $homeDir . " to " . $tmpPath);
		}

		if(!mkdir($homeDir) || !chmod($homeDir, $stat['mode'] & 07777) || !chown($homeDir, $stat['uid']) || !chgrp($homeDir, $stat['gid'])) {
			throw new \Exception("Could not create " . $homeDir . ". The mailbox was left in " . $tmpPath);
		}

		echo "Moving " . $tmpPath . " to " . $maildir . "\n";
		if(!rename($tmpPath, rtrim($maildir, '/'))) {
			throw new \Exception("Could not move " . $tmpPath . " to " . $maildir . ". The mailbox was left in " . $tmpPath);
		}

		foreach($homeFiles as $pattern) {
			foreach(glob($maildir . $pattern) ?: [] as $file) {
				$target = $home . basename($file);
				echo "Moving " . $file . " to " . $target . "\n";
				if(!rename($file, $target)) {
					throw new \Exception("Could not move " . $file . " to " . $target);
				}
			}
		}
	}

	/**
	 * Run a shell command and throw an exception when it fails
	 *
	 * @param string $cmd
	 * @param int[] $okCodes Exit codes that don't count as failure
	 * @return int The exit code
	 * @throws \Exception
	 */
	private function exec(string $cmd, array $okCodes = [0]): int {
		echo "Running " . $cmd . "\n";
		system($cmd, $return);

		if (!in_array($return, $okCodes, true)) {
			throw new \Exception("Command failed with status " . $return);
		}

		return $return;
	}

	/**
	 * Move mailboxes from the old layout, where the mail was stored directly in the
	 * home directory, to a layout with a separate "Maildir" folder inside the home
	 * directory:
	 *
	 * Old: /var/mail/vhosts/example.com/user/{cur,new,tmp,.Sent,...}
	 * New: /var/mail/vhosts/example.com/user/Maildir/{cur,new,tmp,.Sent,...}
	 *
	 * For each mailbox it:
	 *
	 * 1. Leaves the files alone if the Maildir folder already exists, so the script can safely be run again.
	 * 2. Moves the home directory to a temporary folder in /var/mail/vhosts/tmp/, creates a new empty
	 *    home directory with the same owner, group and mode, and moves the old one back into it as "Maildir".
	 * 3. Moves the Dovecot files that belong in the home directory and not in the Maildir (sieve,
	 *    .dovecot.sieve, .dovecot.lda-dupes, .dovecot.svbin and .customflags) back to the home directory.
	 *    Files that don't exist are skipped.
	 * 4. Sets the mailbox's maildir property to "example.com/user/Maildir/" and saves it.
	 * 5. Flushes the Dovecot auth cache of the user, so Dovecot uses the new path right away.
	 *
	 * Before a mailbox is moved, its IMAP and POP3 sessions are disconnected with "doveadm kick". Other
	 * mailboxes stay available, so Dovecot doesn't need to be reloaded. When done, the temporary folder is
	 * removed. Mailboxes without a directory on disk, or that were already converted, only get the new
	 * maildir path in the database.
	 *
	 * Dovecot must take the mail location of each user from the maildir column in SQL, not from a global
	 * mail_location setting. Make a backup of /var/mail/vhosts first. Mail delivered to a mailbox while it is
	 * being moved can end up in the old location, so run it at a quiet moment. It must run as root because it moves files
	 * owned by the vmail user. Every move is printed before it runs and the script stops at the first
	 * one that fails. The error says where the mailbox was left if it is still in the temporary folder.
	 *
	 * Without parameters all mailboxes are converted. Use --domain to convert only the mailboxes of one
	 * domain or --username to convert a single mailbox.
	 *
	 * As root:
	 *
	 * ./cli.php community/maildomains/Script/correctMaildirPaths [--domain=example.com] [--username=user@example.com]
	 *
	 * @param array{domain?: string, username?: string} $params
	 *   - domain: only convert mailboxes of this domain. Throws NotFound if the domain doesn't exist.
	 *   - username: only convert the mailbox with this username (full e-mail address)
	 * @return void
	 * @throws NotFound
	 */
	public function correctMaildirPaths(array $params) {

		if(!Environment::get()->isCli() || (function_exists('posix_geteuid') && posix_geteuid() !== 0)) {
			throw new \Exception("Only run this as root on CLI");
		}

		$mailboxes = model\Mailbox::find();

		if(!empty($params['domain'])) {
			$domainModel = model\Domain::find()->where("domain", "=", $params['domain'])->single();
			if(!$domainModel) {
				throw new NotFound("Domain not found: " . $params['domain']);
			}

			$mailboxes->andWhere('domainId', '=', $domainModel->id);
		}

		if(!empty($params['username'])) {
			$mailboxes->andWhere('username', '=',$params['username']);
		}

		$rootDir = '/var/mail/vhosts/';
		$tmp = $rootDir . "tmp/" . uniqid() . '/';
		if(!mkdir($tmp, 0700, true)) {
			throw new \Exception("Could not create temporary folder " . $tmp);
		}

		// Dovecot files that belong in the home directory and not in the Maildir
		$homeFiles = ['sieve', '.dovecot.sieve*', '.dovecot.lda-dupes*', '.dovecot.svbin', '.customflags'];

		foreach($mailboxes as $mailbox) {

			echo "Converting " . $mailbox->username . "\n";

			$localPart = explode('@', $mailbox->username)[0];

			$domainModel = model\Domain::findById($mailbox->domainId);

			$domain = $domainModel->domain;

			if(!$this->isSafePathSegment($localPart) || !$this->isSafePathSegment($domain)) {
				throw new \Exception("Invalid characters in username or domain of mailbox " . $mailbox->username);
			}

			$home = $rootDir . $domain . '/' . $localPart . '/';
			$maildir = $home . 'Maildir/';

			if(is_dir($maildir)) {
				echo "Already converted.\n";
			} else if(!is_dir($home)) {
				echo "Maildir does not exist\n";
			} else {
				// Disconnect the user so no IMAP or POP3 session uses the folder while it's moved.
				// Exit code 68 means the user wasn't connected.
				$this->exec("doveadm kick " . escapeshellarg($mailbox->username), [0, 68]);
				$this->moveToMaildir($home, $maildir, $tmp . $localPart . '@' . $domain, $homeFiles);
			}

			$mailbox->maildir = substr($maildir, strlen($rootDir));
			if(!$mailbox->save()) {
				throw new \Exception("Could not save mailbox " . $mailbox->username);
			}

			// Dovecot caches the user data from SQL. Flush it so the new path is used right away.
			$this->exec("doveadm auth cache flush " . escapeshellarg($mailbox->username));
		}

		if(!rmdir($tmp)) {
			echo "Warning: could not remove temporary folder " . $tmp . "\n";
		}

		echo "Done!\n";
	}
}