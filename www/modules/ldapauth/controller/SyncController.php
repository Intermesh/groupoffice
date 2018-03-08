<?php

namespace GO\Ldapauth\Controller;

class SyncController extends \GO\Base\Controller\AbstractController {

	protected function allowGuests() {
		return array("users", "lookupuser", "groups");
	}

	protected function actionLookupUser($params) {

		$this->requireCli();
		$this->checkRequiredParameters(array('uid'), $params);

		$la = new \GO\Ldapauth\Authenticator();

		$ldapConn = \GO\Base\Ldap\Connection::getDefault();

		$result = $ldapConn->search(\GO\Ldapauth\LdapauthModule::getPeopleDn($params['uid']), $la->getUserSearchQuery($params['uid']));

		$record = $result->fetch();
		$attr = $record->getAttributes();
	}

	/**
	 * 
	 * php groupofficecli.php -r=ldapauth/sync/users --delete=1 --max_delete_percentage=34 --dry=1
	 * 
	 * @param type $params
	 * @throws Exception
	 */
	protected function actionUsers($params) {


		$this->requireCli();
		\GO::session()->runAsRoot();

		$dryRun = !empty($params['dry']);

		if ($dryRun)
			echo "Dry run enabled.\n\n";

		$la = new \GO\Ldapauth\Authenticator();

		$ldapConn = \GO\Base\Ldap\Connection::getDefault();

		$result = $ldapConn->search(\GO\Ldapauth\LdapauthModule::getPeopleDn(), $la->getUserSearchQuery());

		//keep an array of users that exist in ldap. This array will be used later for deletes.
		//admin user is not in ldap but should not be removed.
		$usersInLDAP = array(1);

		$i = 0;
		while ($record = $result->fetch()) {
			$i++;


			try {
				if (!$dryRun) {
					$user = $la->syncUserWithLdapRecord($record);
					if (!$user) {
						//could be expluded from LDAP.
//						echo "Failed syncing user. Enable and check debug log for more info.";
//						echo "Failed LDAP record: ".var_export($record->getAttributes(), true)."\n";
						continue;
					}
					$username = $user->username;
				} else {
					$attr = $la->getUserAttributes($record);
					$username = $attr['username'];
					$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $attr['username']);
				}

				if (!$dryRun)
					$this->fireEvent("ldapsyncuser", array($user, $record));

				echo "Synced " . $username . "\n";
			} catch (\Exception $e) {
				echo "ERROR:\n";
				echo (string) $e;

				echo "LDAP record:";
				var_dump($record->getAttributes());
			}



			if ($user)
				$usersInLDAP[] = $user->id;

//			if($i==100)
//				exit("Reached 100. Exitting");
		}



		$stmt = \GO\Base\Model\User::model()->find();

		$totalInGO = $stmt->rowCount();
		$totalInLDAP = count($usersInLDAP);

		echo "Users in Group-Office: " . $totalInGO . "\n";
		echo "Users in LDAP: " . $totalInLDAP . "\n";

		if (!empty($params['delete'])) {
			$percentageToDelete = round((1 - $totalInLDAP / $totalInGO) * 100);

			$maxDeletePercentage = isset($params['max_delete_percentage']) ? intval($params['max_delete_percentage']) : 5;

			if ($percentageToDelete > $maxDeletePercentage)
				die("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the users (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " users)\n");

			while ($user = $stmt->fetch()) {
				if (!in_array($user->id, $usersInLDAP)) {
					echo "Deleting " . $user->username . "\n";
					if (!$dryRun)
						$user->delete();
				}
			}
		}

		echo "Done\n\n";

		//var_dump($attr);
	}

	/**
	 * 
	 * php groupofficecli.php -r=ldapauth/sync/groups --delete=1 --max_delete_percentage=34 --dry=1
	 * 
	 * @param type $params
	 * @throws Exception
	 */
	protected function actionGroups($params) {


		$this->requireCli();
		\GO::session()->runAsRoot();

		$dryRun = !empty($params['dry']);

		if ($dryRun)
			echo "Dry run enabled.\n\n";

		$ldapConn = \GO\Base\Ldap\Connection::getDefault();

		if (empty(\GO::config()->ldap_groupsdn))
			throw new \Exception('$config[\'ldap_groupsdn\'] is not set!');

//		$result = $ldapConn->search(\GO::config()->ldap_groupsdn, 'cn=*');
		if (empty(\GO::config()->ldap_groups_search)) {
			\GO::config()->ldap_groups_search = 'cn=*';
		}
		$result = $ldapConn->search(\GO::config()->ldap_groupsdn, \GO::config()->ldap_groups_search);

//		$record = $result->fetch();
//		$attr = $record->getAttributes();
//		var_dump($attr);
//		exit();
//		
		//keep an array of groups that exist in ldap. This array will be used later for deletes.
		//admin group is not in ldap but should not be removed.
		$groupsInLDAP = array(\GO::config()->group_root, \GO::config()->group_everyone, \GO::config()->group_internal);

		$i = 0;
		while ($record = $result->fetch()) {
			$i++;

			try {
				$groupname = $record->cn[0];

				if (empty($groupname)) {
					throw new \Exception("Empty group name in LDAP record!");
				}

				$group = \GO\Base\Model\Group::model()->findByName($groupname);
				if (!$group) {

					echo "Creating group '" . $groupname . "'\n";

					$group = new \GO\Base\Model\Group();
					$group->name = $groupname;
					if (!$dryRun && !$group->save()) {
						echo "Error saving group: " . implode("\n", $group->getValidationErrors());
					}
				} else {
					echo "Group '" . $groupname . "' exists\n";
				}

				$usersInGroup = array();


				$members = array();

				$ad = false;
				if (isset($record->memberuid)) {
					//for openldap
					$members = $record->memberuid;
				} else if (isset($record->member)) {
					//for Active Directory
					$members = $record->member;
					$ad = true;
				} else {
					echo "Error: no member array found in group";
					continue;
				}

				foreach ($members as $username) {

					if ($ad) {
						$username = $this->queryActiveDirectoryUser($ldapConn, $username);
						if (!$username) {
							continue;
						}
					}

					$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
					if (!$user) {
						echo "Error: user '" . $username . "' does not exist in Group-Office\n";
					} else {
						echo "Adding user '$username'\n";
						if (!$dryRun)
							$group->addUser($user->id);

						$usersInGroup[] = $user->id;
					}
				}

				echo "Removing users from group\n";

				$findParams = \GO\Base\Db\FindParams::newInstance();
				$findParams->getCriteria()->addInCondition('user_id', $usersInGroup, 'link_t', true, true);
				$usersToRemove = $group->users($findParams);
				foreach ($usersToRemove as $user) {
					echo "Removing user '" . $user->username . "'\n";

					if (!$dryRun)
						$group->removeUser($user->id);
				}


				if (!$dryRun) {
					$this->fireEvent("ldapsyncgroup", array($group, $record));
				}

				echo "Synced " . $groupname . "\n";
			} catch (\Exception $e) {
				echo "ERROR:\n";
				echo (string) $e;

				echo "LDAP record:";
				var_dump($record->getAttributes());
			}



			if ($group)
				$groupsInLDAP[] = $group->id;

//			if($i==100)
//				exit("Reached 100. Exitting");
		}



		$stmt = \GO\Base\Model\Group::model()->find();

		$totalInGO = $stmt->rowCount();
		$totalInLDAP = count($groupsInLDAP);

		echo "Groups in Group-Office: " . $totalInGO . "\n";
		echo "Groups in LDAP: " . $totalInLDAP . "\n";

		if (!empty($params['delete'])) {
			$percentageToDelete = round((1 - $totalInLDAP / $totalInGO) * 100);

			$maxDeletePercentage = isset($params['max_delete_percentage']) ? intval($params['max_delete_percentage']) : 5;

			if ($percentageToDelete > $maxDeletePercentage)
				die("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the groups (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");

			while ($group = $stmt->fetch()) {
				if (!in_array($group->id, $groupsInLDAP)) {
					echo "Deleting " . $group->name . "\n";
					if (!$dryRun)
						$group->delete();
				}
			}
		}

		echo "Done\n\n";

		//var_dump($attr);
	}

	private function queryActiveDirectoryUser($ldapConn, $groupMember) {
		$parts = preg_split('~(?<!\\\),~', $groupMember);
		$query = str_replace('\\,', ',', array_shift($parts));
		$query = str_replace('(', '\\(', $query);
		$query = str_replace(')', '\\)', $query);

		$searchDn = implode(',', $parts);


		$accountResult = $ldapConn->search($searchDn, $query);
		$record = $accountResult->fetch();
		return $record->sAMAccountName[0];
	}

}
