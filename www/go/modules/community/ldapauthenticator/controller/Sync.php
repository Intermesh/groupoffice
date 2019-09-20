<?php
namespace go\modules\community\ldapauthenticator\controller;

use go\core\Controller;
use go\modules\community\ldapauthenticator\model\Server;
use go\core\exception\NotFound;
use go\core\ldap\Record;
use go\core\model\Group;
use go\core\ldap\Connection;
use go\core\model\UserGroup;
use go\core\model\User;
use go\modules\community\ldapauthenticator\Module;
use go\core\event\EventEmitterTrait;

class Sync extends Controller {

  const EVENT_SYNC_USER = 'syncuser';

  const EVENT_SYNC_GROUP = 'syncuser';

	use EventEmitterTrait;

  /**
   * docker-compose exec --user www-data groupoffice-master php /usr/local/share/groupoffice/cli.php community/ldapauthenticator/Sync/users --id=2 --dryRun=1 --delete=1 --maxDeletePercentage=50
   */
  public function users($id, $dryRun = false, $delete = false, $maxDeletePercentage = 5) {
    //objectClass	inetOrgPerson)
    $server = Server::findById($id);
    if(!$server) {
      throw new NotFound();
    }

    $this->serverId = $id;

    $connection = $server->connect();

    $usersInLDAP = [1];
		
		$records = Record::find($connection, $server->peopleDN, $server->syncUsersQuery);
    
    $i = 0;
    foreach($records as $record) {
      $i++;
      $username = $record->uid[0] ?? $record->SAMAccountName[0];

      if (empty($username)) {
        throw new \Exception("Empty group name in LDAP record!");
      }
      $user = User::find()->where(['username' => $username])->single();
      if (!$user) {

        echo "Creating user '" . $username . "'\n";

        $user = new User();
        $user->username = $username;
        
      } else {
        echo "User '" . $username . "' exists\n";    
      }

      Module::ldapRecordToUser($username, $record, $user);

      $this->fireEvent(self::EVENT_SYNC_USER, $user, $record);

      if (!$dryRun) {
        if($user->isModified() && !$user->save()) {
          echo "Error saving user: " . implode("\n", $user->getValidationErrors());
          continue;
        }

        go()->getDbConnection()
          ->replace('ldapauth_server_user_sync', ['serverId' => $id, 'userId' => $user->id])->execute();
      }      

			echo "Synced " . $username . "\n";		

			$usersInLDAP[] = $user->id;
		}

		if ($delete) {
			$this->deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun);
		}

    echo "Done\n\n";
  }


  private function deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun) {
    $users = User::find()
      ->join('ldapauth_server_user_sync', 's', 's.userId = u.id')
      ->where('serverId', '=', $this->serverId)->execute();
		$totalInGO = $users->rowCount();
		$totalInLDAP = count($usersInLDAP);

		echo "Groups in Group-Office: " . $totalInGO . "\n";
    echo "Groups in LDAP: " . $totalInLDAP . "\n";
    
    $percentageToDelete = round((1 - $totalInLDAP / $totalInGO) * 100);		
    if ($percentageToDelete > $maxDeletePercentage)
      throw new \Exception("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the groups (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");

    foreach($users as $user) {
      if (!in_array($user->id, $usersInLDAP)) {
        echo "Deleting " . $user->username . "\n";
        if (!$dryRun)
          $user->delete();
      }
    }
  }
  
  private $serverId;

  /**
   * docker-compose exec --user www-data groupoffice-master php /usr/local/share/groupoffice/cli.php community/ldapauthenticator/Sync/groups --id=2 --dryRun=1 --delete=1 --maxDeletePercentage=50
   */
  public function groups($id, $dryRun = false, $delete = false, $maxDeletePercentage = 5) {

    $server = Server::findById($id);
    if(!$server) {
      throw new NotFound();
    }

    $this->serverId = $id;

    $connection = $server->connect();

    $groupsInLDAP = [Group::ID_ADMINS, Group::ID_EVERYONE, Group::ID_INTERNAL];
		
		$records = Record::find($connection, $server->peopleDN, $server->syncGroupsQuery);
    
    $i = 0;
    foreach($records as $record) {
      $i++;
      $name = $record->cn[0];

      if (empty($name)) {
        throw new \Exception("Empty group name in LDAP record!");
      }
      $group = Group::find()->where(['name' => $name, 'isUserGroupFor' => null])->single();
      if (!$group) {

        echo "Creating group '" . $name . "'\n";

        $group = new Group();
        $group->name = $name;
        if (!$dryRun && !$group->save()) {
          echo "Error saving group: " . implode("\n", $group->getValidationErrors());
        }
      } else {
        echo "Group '" . $name . "' exists\n";    
      }

    
      // Clear existing users
      $group->users = [];
    

      $members = $this->getGroupMembers($record, $connection);      

      foreach ($members as $username) {
        $user = \go\core\model\User::find()->where(['username' => $username])->single();
        if (!$user) {
          echo "Error: user '" . $username . "' does not exist in Group-Office\n";
        } else {
          echo "Adding user '$username'\n";
          $group->users[] = $user->id; //(new UserGroup())->setValue('userId', $user->id);
        }
      }

      $this->fireEvent(self::EVENT_SYNC_GROUP, $group, $record);

      if (!$dryRun) {
        if(!$group->save()) {
          throw new \Excpetion("Could not save group");
        }

        go()->getDbConnection()
          ->replace('ldapauth_server_group_sync', ['serverId' => $id, 'groupId' => $group->id])->execute();

      }

			echo "Synced " . $name . "\n";		

			$groupsInLDAP[] = $group->id;
		}

		if ($delete) {
			$this->deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun);
		}

    echo "Done\n\n";
    
    // go()->getDebugger()->printEntries();

		//var_dump($attr);
  }

  private function deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun) {
    $groups = Group::find()
      ->join('ldapauth_server_group_sync', 's', 's.groupId = g.id')
      ->where('serverId', '=', $this->serverId)->execute();

		$totalInGO = $groups->rowCount();
		$totalInLDAP = count($groupsInLDAP);

		echo "Groups in Group-Office: " . $totalInGO . "\n";
    echo "Groups in LDAP: " . $totalInLDAP . "\n";
    
    $percentageToDelete = round((1 - $totalInLDAP / $totalInGO) * 100);		
    if ($percentageToDelete > $maxDeletePercentage)
      throw new \Exception("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the groups (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");

    foreach($groups as $group) {
      if (!in_array($group->id, $groupsInLDAP)) {
        echo "Deleting " . $group->name . "\n";
        if (!$dryRun)
          $group->delete();
      }
    }
  }
  
  private function getGroupMembers(Record $record, Connection $ldapConn) {
    $members = [];
    if (isset($record->memberuid)) {
      //for openldap
      return $record->memberuid;
    } else if (isset($record->member)) {
      //for Active Directory
      foreach ($record->member as $username) {      
        $username = $this->queryActiveDirectoryUser($ldapConn, $username);
        if (!$username) {
          continue;
        }
        $members[] = $username;
      }
      return $members;
    } else {
      echo "Error: no member array found in group";
      return [];
    }
    
  }

	private function queryActiveDirectoryUser(Connection $ldapConn, $groupMember) {
		$parts = preg_split('~(?<!\\\),~', $groupMember);
		$query = str_replace('\\,', ',', array_shift($parts));
		$query = str_replace('(', '\\(', $query);
		$query = str_replace(')', '\\)', $query);

		$searchDn = implode(',', $parts);

		$accountResult = Record::find($ldapConn, $searchDn, $query);
    $record = $accountResult->fetch();
    
		return $record->SAMAccountName[0] ?? $record->uid[0];
	}
}