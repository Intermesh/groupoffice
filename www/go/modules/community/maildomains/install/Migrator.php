<?php


namespace go\modules\community\maildomains\install;


use go\core\db\DbException;
use go\core\fs\Folder;
use go\core\util\DateTime;
use go\modules\community\maildomains\model\DkimKey;
use go\modules\community\maildomains\model\Domain;

final class Migrator
{
	/**
	 * @throws DbException
	 */
	public function migrate()
	{
		go()->getDbConnection()->beginTransaction();

		go()->getDbConnection()->exec("INSERT INTO community_maildomains_domain (id, userId, domain, description, maxAliases, maxMailboxes,totalQuota,defaultQuota,transport,backupMx,active,aclId, createdBy, modifiedBy, createdAt) 
SELECT id, user_id, domain, description, max_aliases, max_mailboxes, (1024 * total_quota), (1024 * default_quota),transport,backupmx, active, acl_id, 1, 1, NOW() FROM pa_domains;");
		go()->getDbConnection()->exec("INSERT INTO community_maildomains_mailbox (id, domainId, username, password, smtpAllowed, description, maildir, homedir, quota, createdBy, createdAt, modifiedBy, modifiedAt , active, autoExpunge)
SELECT id, domain_id, username, password, smtpAllowed, name, maildir, homedir, (1024 * quota), 1, NOW(), 1, NOW(), active, 0 FROM pa_mailboxes;");
		go()->getDbConnection()->exec("INSERT INTO community_maildomains_alias (id, domainId, address, goto, createdBy, createdAt, modifiedBy, modifiedAt, active) SELECT id, domain_id, address, goto, 1, NOW(), 1, NOW(), active FROM pa_aliases;");

		go()->getDbConnection()->commit();

	}

	/**
	 * Migrates DKIM keys from filesystem to database.
	 * It can't run automatically as www-data has no access to the files. This must be changed first.
	 *
	 * @param $folder
	 * @param $selector
	 * @return void
	 * @throws \Exception
	 */
	public function migrateDKIM($folder = "/var/lib/rspamd/dkim", $selector = "mail") {
		$dkimRoot = new Folder($folder);

		if(!$dkimRoot->exists()) {
			return;
		}

		$domains = Domain::find();
		foreach($domains as $domain) {
			$keyFile = $dkimRoot->getFile($domain->domain  . "/" . $selector . ".private");
			if(!$keyFile->exists()) {
				continue;
			}

			$dkim = (new DkimKey($domain));
			$dkim->enabled = true;
			$dkim->selector = $selector;
			$dkim->setPrivateKey($keyFile->getContents());
			$domain->dkim[] = $dkim;

			if(!$domain->save()) {
				echo "Failed to save DKIM key for domain ".$domain->domain."\n";
				echo $domain->getValidationErrorsAsString() ."\n";
			} else {
				echo "Saved DKIM key for domain ".$domain->domain."\n";
			}

			echo "----\n\n";
		}

	}
}