<?php /** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpComposerExtensionStubsInspection */

namespace go\modules\community\ldapauthenticator\cli\controller;

use Exception;
use go\core\Controller;
use go\core\event\EventEmitterTrait;
use go\core\exception\NotFound;
use go\core\ldap\Connection;
use go\core\ldap\Record;
use go\core\model\Group;
use go\core\model\User;
use go\core\orm\EntityType;
use go\modules\community\ldapauthenticator\model\Server;
use go\modules\community\ldapauthenticator\Module;

class Sync extends Controller
{

	const EVENT_SYNC_USER = 'syncuser';

	const EVENT_SYNC_GROUP = 'syncuser';

	use EventEmitterTrait;

	private $domains;


	/**
	 * Test a single username
	 *
	 * eg.
	 * sudo -u www-data php /usr/share/groupoffice/cli.php community/ldapauthenticator/Sync/test --id=1 --username=john --debug
	 *
	 * @param $id
	 * @param $username
	 * @throws NotFound
	 * @throws Exception
	 */
	public function test($params)
	{

		extract($this->checkParams($params, ['id', 'username']));
		//objectClass	inetOrgPerson)
		$server = Server::findById($id);
		if (!$server) {
			throw new NotFound("No LDAP config found with id = " . $id);
		}

		$this->serverId = $id;

		$connection = $server->connect();

		echo "Connected\n";

		$this->domains = array_map(function ($d) {
			return $d->name;
		}, $server->domains);

		$records = Record::find($connection, $server->peopleDN, $server->usernameAttribute . "=" . $username, 100);

		foreach ($records as $record) {

//			var_dump($record->getAttributes());
//
//			var_dump($record->getObjectClass());


			echo $record->getDn() . "\n";
//
			$user = $this->ldapRecordToUser($record, $server, false);
//
			echo "User: " . $user->username . "\n";
//			var_dump($record->memberOf);
		}
	}


	/**
	 * docker compose exec --user www-data groupoffice php /usr/local/share/src/www/cli.php community/ldapauthenticator/Sync/users --id=1 --dryRun=1 --delete=1 --maxDeletePercentage=50
	 * @throws NotFound
	 * @throws Exception
	 */
	public function users($params)
	{
		extract($this->checkParams($params, ['id', 'dryRun' => false, 'delete' => false, 'maxDeletePercentage' => 5]));
		//objectClass	inetOrgPerson)
		$server = Server::findById($id);
		if (!$server) {
			throw new NotFound("No LDAP config found with id = " . $id);
		}

		$this->serverId = $id;

		$connection = $server->connect();

		if (!empty($xserver->username)) {
			if (!$connection->bind($server->username, $server->getPassword())) {
				throw new Exception("Invalid password given for '" . $server->username . "'");
			} else {
				go()->debug("Authenticated with user '" . $server->username . '"');
			}
		}

		$usersInLDAP = [];

		$this->domains = array_map(function ($d) {
			return $d->name;
		}, $server->domains);

		$records = Record::find($connection, $server->peopleDN, $server->syncUsersQuery);

		$i = 0;
		foreach ($records as $record) {
			$i++;
			$user = $this->ldapRecordToUser($record, $server, $dryRun);
			if ($user) {
				$usersInLDAP[] = $user->id;
			}

			//push changes after each user
			EntityType::push();
		}

		if ($delete) {
			$this->deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun);
		}

		$this->output("Done\n\n");
	}

	/**
	 * @throws Exception
	 */
	private function ldapRecordToUser(Record $record, Server $server, $dryRun)
	{

		$username = $this->getGOUserName($record, $server);

		if (empty($username)) {
			$this->output("Skipping record. Could not determine username for record: " . $record->getDn());
			return false;
		}

		$user = User::find()->where(['username' => $username]);

		if (!empty($record->mail[0])) {
			$user->orWhere(['email' => $record->mail[0]]);
		}
		$user = $user->single();

		if (!$user) {
			$this->output("Creating user '" . $username . "'");

			$user = new User();
			$user->username = $username;

		} else {
			$this->output("User '" . $username . "' exists");
		}

		if ($user->hasPassword()) {
			//password in database is not needed and clearing it improves security
			$user->clearPassword();
		}

		Module::ldapRecordToUser($username, $record, $user);

		$this->fireEvent(self::EVENT_SYNC_USER, $user, $record);

		if (!$dryRun) {
			if ($user->isModified() && !$user->save()) {
				echo "Error saving user: " . var_export($user->getValidationErrors(), true);
				return false;
			}

			go()->getDbConnection()
				->replace('ldapauth_server_user_sync', ['serverId' => $this->serverId, 'userId' => $user->id])->execute();
		}

		$this->output("Synced " . $username);

		return $user;
	}

	private function getGOUserName(Record $record, Server $server)
	{
		$username = $record->{$server->usernameAttribute}[0] ?? null;

		if (!$username) {
			go()->debug("No username found in record: ");
			go()->debug($record->getAttributes());
			return false;
		}

		$dn = ldap_explode_dn($record->getDn(), 0);

		go()->debug($dn);

		/*
		  array(5) {
		  ["count"]=>
		  int(4)
		  [0]=>
		  string(19) "cn=John A. Zoidberg"
		  [1]=>
		  string(9) "ou=people"
		  [2]=>
		  string(16) "dc=planetexpress"
		  [3]=>
		  string(6) "dc=com"
		}*/

		//try to determine domain that fits the user best
		$domain = "";
		foreach ($dn as $v) {
			if (substr($v, 0, 3) == 'dc=') {
				if ($domain != "") {
					$domain .= '.';
				}
				$domain .= substr($v, 3);
			}
		}

		$mailDomain = isset($record->mail[0]) ? explode('@', $record->mail[0])[1] : null;

		if (empty($domain) || !in_array($domain, $this->domains)) {
			go()->info("Using domain from mail property for " . $username);
			if (empty($mailDomain)) {
				go()->info("No email property available!");
				return false;
			}
			$domain = $mailDomain;
		}

		//fall back on the first if no domain was found from dn or mail address.
		if (!in_array($domain, $this->domains)) {
			$domain = $this->domains[0];
		}

		go()->debug("GO username should be: " . $username . '@' . $domain);

		return $username . '@' . $domain;

	}


	/**
	 * @throws Exception
	 */
	private function deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun)
	{
		$users = User::find(['id', 'username'])
			->join('ldapauth_server_user_sync', 's', 's.userId = u.id')
			->where('serverId', '=', $this->serverId)->execute();
		$totalInGO = $users->rowCount();
		$totalInLDAP = count($usersInLDAP);

		$this->output("Users in Group-Office: " . $totalInGO);
		$this->output("Users in LDAP: " . $totalInLDAP);

		$deleteUsers = [];
		foreach ($users as $user) {
			if (!in_array($user->id, $usersInLDAP)) {
				$deleteUsers[] = [$user->id, $user->username];
			}
		}

		$this->logDeletes($deleteUsers, $totalInLDAP, $maxDeletePercentage, $totalInGO);

		if (!empty($deleteUsers)) {
			// delete one by one to prevent a big locking transaction
			foreach ($deleteUsers as $u) {
				$this->output("Deleting: " . $u[1]);
				if (!$dryRun) {
					User::delete(['id' => $u[0]]);
				}
			}
		}
	}

	private $serverId;

	private function output($str)
	{
		go()->debug($str);

		if (!go()->getEnvironment()->isCron()) {
			echo $str . "\n";
		}
	}

	/**
	 * docker compose exec --user www-data groupoffice php /usr/local/share/groupoffice/cli.php community/ldapauthenticator/Sync/groups --id=2 --dryRun=1 --delete=1 --maxDeletePercentage=50
	 * @throws Exception
	 */
	public function groups($params)
	{

		extract($this->checkParams($params, ['id', 'dryRun' => false, 'delete' => false, 'maxDeletePercentage' => 5]));

		$server = Server::findById($id);
		if (!$server) {
			throw new NotFound();
		}

		$this->serverId = $id;

		$connection = $server->connect();

		$this->domains = array_map(function ($d) {
			return $d->name;
		}, $server->domains);

		if (!empty($server->username)) {
			if (!$connection->bind($server->username, $server->getPassword())) {
				throw new Exception("Invalid password given for '" . $server->username . "'");
			} else {
				go()->debug("Authenticated with user '" . $server->username . '"');
			}
		}

		$groupsInLDAP = [];

		$records = Record::find($connection, $server->groupsDN, $server->syncGroupsQuery);

		foreach ($records as $record) {
			$name = $record->cn[0];

			go()->debug($record->getAttributes());

			if (empty($name)) {
				throw new Exception("Empty group name in LDAP record!");
			}
			$group = Group::find()->where(['name' => $name, 'isUserGroupFor' => null])->single();
			if (!$group) {

				$this->output("Creating group '" . $name . "'");

				$group = new Group();
				$group->name = $name;
				if (!$dryRun && !$group->save()) {
					echo "Error saving group: " . implode("\n", $group->getValidationErrors());
				}
			} else {
				$this->output("Group '" . $name . "' exists");
			}


			// Clear existing users
			$group->users = [];

			$members = $this->getGroupMembers($record, $connection, $server);

			foreach ($members as $u) {
				$userQuery = User::find(['id'])->where(['username' => $u['username']]);
				if(!empty($u['email'])) {
					$userQuery->orWhere(['email' => $u['email']]);
				}
				$user = $userQuery->single();
				if (!$user) {
					$this->output("Error: user '" . $u['username'] . "' does not exist in Group-Office");
				} else {
					$this->output("Adding user '" . $u['username'] . "'");
					if (!in_array($user->id, $group->users)) {
						$group->users[] = $user->id;
					}
				}
			}

			$this->fireEvent(self::EVENT_SYNC_GROUP, $group, $record);

			if (!$dryRun) {
				if (!$group->save()) {
					throw new Exception("Could not save group");
				}

				go()->getDbConnection()
					->replace('ldapauth_server_group_sync', ['serverId' => $id, 'groupId' => $group->id])->execute();

			}

			$this->output("Synced " . $name);

			//push changes after each user
			EntityType::push();

			$groupsInLDAP[] = $group->id;
		}


		if ($delete) {
			$this->deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun);
		}

		$this->output("Done");
	}

	/**
	 * @throws Exception
	 */
	private function deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun)
	{
		$groups = Group::find(['id', 'name'])
			->join('ldapauth_server_group_sync', 's', 's.groupId = g.id')
			->where('serverId', '=', $this->serverId)->execute();

		$totalInGO = $groups->rowCount();
		$totalInLDAP = count($groupsInLDAP);

		$this->output("Groups in Group-Office: " . $totalInGO);
		$this->output("Groups in LDAP: " . $totalInLDAP);

		$deleteGroups = [];
		foreach ($groups as $group) {
			if (!in_array($group->id, $groupsInLDAP)) {
				$deleteGroups[] = [$group->id, $group->name];
			}
		}

		$this->logDeletes($deleteGroups, $totalInLDAP, $maxDeletePercentage, $totalInGO);

		if (!empty($deleteGroups)) {
			foreach ($deleteGroups as $g) {

				$this->output("Deleting: " . $g[1]);

				if (!$dryRun) {
					Group::delete(['id' => $g['id']]);
				}
			}
		}
	}


	private function getGroupMembers(Record $record, Connection $ldapConn, Server $server): array
	{
		$members = [];
		if (isset($record->memberuid)) {
			//for openldap
			foreach ($record->memberuid as $uid) {
				$accountResult = Record::find($ldapConn, $server->peopleDN, 'uid=' . $uid);
				if ($r = $accountResult->fetch()) {
					$members[] = ['username' => $this->getGOUserName($r, $server), 'email' => $r->mail[0] ?? null];
				}

			}
		} else if (isset($record->member)) {
			//for Active Directory
			foreach ($record->member as $username) {
				go()->debug("Member: " . $username);
				$u = $this->queryActiveDirectoryUser($ldapConn, $username, $server);
				if (!$u || !$u['username']) {
					go()->debug($u);
					$this->output("Skipping '$username'. Could not find GO user");
					continue;
				}
				$members[] = $u;
			}
		} else {
			$this->output("Error: no member array found in group");
			return [];
		}

		return $members;
	}

	private function queryActiveDirectoryUser(Connection $ldapConn, $groupMember, Server $server): ?array
	{
		$parts = preg_split('~(?<!\\\),~', $groupMember);
		$query = str_replace('\\,', ',', array_shift($parts));
		$query = str_replace('(', '\\(', $query);
		$query = str_replace(')', '\\)', $query);

		$searchDn = implode(',', $parts);

		$accountResult = Record::find($ldapConn, $searchDn, $query);
		$record = $accountResult->fetch();
		if (!$record) {
			$this->output("Skipping '$groupMember'. Could not find GO it in LDAP with query: " . $query);
			return null;
		}

		//Sometimes mail record doesn't exist. It can't find users by mail address in that case
		return ['username' => $this->getGOUserName($record, $server), 'email' => $record->mail[0] ?? null];
	}

	/**
	 * @param array $deleteIds
	 * @param int $totalInLDAP
	 * @param $maxDeletePercentage
	 * @param int $totalInGO
	 * @return void
	 * @throws Exception
	 */
	private function logDeletes(array $deleteIds, int $totalInLDAP, $maxDeletePercentage, int $totalInGO): void
	{
		$deleteCount = count($deleteIds);

		$this->output("Delete count: " . $deleteCount);

		$percentageToDelete = $totalInLDAP > 0 ? round(($deleteCount / $totalInGO) * 100, 2) : 0;

		$this->output("Delete percentage: " . $percentageToDelete . "%, Max: " . $maxDeletePercentage . '%');

		if ($percentageToDelete > $maxDeletePercentage) {
			throw new Exception("Delete Aborted because script was about to delete more then $maxDeletePercentage% (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");
		}
	}
}