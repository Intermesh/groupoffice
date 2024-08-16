<?php


namespace go\modules\community\maildomains\install;


use go\core\db\DbException;
use go\core\util\DateTime;
use go\modules\community\maildomains\model\Domain;

final class Migrator
{
	/**
	 * @throws DbException
	 */
	public function migrate()
	{
		$data = [];
		go()->getDbConnection()->beginTransaction();
		$ds = go()->getDbConnection()->select("*")->from('pa_domains')->orderBy(['id' => 'ASC']);
		foreach($ds->all() as $d) {
			$data[] = [
				'id' => $d['id'],
				'userId' => $d['user_id'],
				'domain' => $d['domain'],
				'description' => $d['description'],
				'maxAliases' => $d['max_aliases'] ?? 0,
				'maxMailboxes' => $d['max_mailboxes'] ?? 0,
				'totalQuota' => $d['total_quota'] ?? 0,
				'defaultQuota' => $d['default_quota'] ?? 0,
				'transport' => $d['transport'],
				'backupMx' => $d['backupmx'],
				'createdAt' => DateTime::createFromFormat('U', $d['ctime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'createdBy' => $d['user_id'],
				'modifiedAt' => DateTime::createFromFormat('U', $d['mtime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'modifiedBy' => $d['user_id'],
				'active' => $d['active'],
				'aclId' => $d['acl_id']
			];
		}
		go()->getDbConnection()->insert('community_maildomains_domain', $data)->execute();
		unset($data);
		unset($ds);
		unset($d);

		$data = [];
		$ms = go()->getDbConnection()->select("*")->from('pa_mailboxes')->orderBy(['id' => 'ASC']);
		foreach($ms->all() as $m) {
			$data[] = [
				'id' => $m['id'],
				'domainId' => $m['domain_id'],
				'username' => $m['username'],
				'password' => $m['password'],
				'smtpAllowed' => $m['smtpAllowed'],
				'name' => $m['name'],
				'maildir' => $m['maildir'],
				'homedir' => $m['homedir'],
				'quota' => $m['quota'] ?? '0',
				'createdBy' => 1,
				'createdAt' => DateTime::createFromFormat('U', $m['ctime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'modifiedBy' => 1,
				'modifiedAt' => DateTime::createFromFormat('U', $m['mtime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'active' => $m['active']
			];
		}
		go()->getDbConnection()->insert('community_maildomains_mailbox', $data)->execute();
		unset($data);
		unset($ms);
		unset($m);

		$data = [];
		$as = go()->getDbConnection()->select("*")->from('pa_aliases')->orderBy(['id' => 'ASC']);
		foreach($as->all() as $a) {
			$data[] = [
				'id' => $a['id'],
				'domainId' => $a['domain_id'],
				'address' => $a['address'],
				'goto' => $a['goto'],
				'createdBy' => 1,
				'createdAt' => DateTime::createFromFormat('U', $a['ctime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'modifiedBy' => 1,
				'modifiedAt' => DateTime::createFromFormat('U', $a['mtime'], new \DateTimeZone(go()->getSystemTimeZone())),
				'active' => $a['active']
			];
		}
		go()->getDbConnection()->insert('community_maildomains_alias', $data)->execute();

		go()->getDbConnection()->commit();
	}
}