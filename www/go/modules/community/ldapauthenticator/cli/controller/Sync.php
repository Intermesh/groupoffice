<?php
namespace go\modules\community\ldapauthenticator\cli\controller;

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
  
  private $domains;


  public function test($id) {
	  //objectClass	inetOrgPerson)
	  $server = Server::findById($id);
	  if(!$server) {
		  throw new NotFound("No LDAP config found with id = ". $id);
	  }

	  $this->serverId = $id;

	  $connection = $server->connect();

	  echo "Connected\n";

	  $records = Record::find($connection, $server->peopleDN, $server->usernameAttribute . "=*");

	  foreach($records as $record) {
	  	echo $record->getDn() . "\n";
	  }
  }

  /**
   * docker-compose exec --user www-data groupoffice-64 php /usr/local/share/src/www/cli.php community/ldapauthenticator/Sync/users --id=1 --dryRun=1 --delete=1 --maxDeletePercentage=50
   */
  public function users($id, $dryRun = false, $delete = false, $maxDeletePercentage = 5) {
    //objectClass	inetOrgPerson)
    $server = Server::findById($id);
    if(!$server) {
      throw new NotFound("No LDAP config found with id = ". $id);
    }

    $this->serverId = $id;

    $connection = $server->connect();

    if (!empty($server->username)) {
			if (!$connection->bind($server->username, $server->getPassword())) {				
				throw new \Exception("Invalid password given for '".$server->username."'");
			} else
			{
				go()->debug("Authenticated with user '" . $server->username . '"');
			}
		}

    $usersInLDAP = [1];

    $this->domains  = array_map(function($d) {return $d->name;}, $server->domains);
		
		$records = Record::find($connection, $server->peopleDN, $server->syncUsersQuery);
    
    $i = 0;
    foreach($records as $record) {
      $i++;
      $username = $this->getGOUserName($record,$server);
      
      if (empty($username)) {
        $this->output("Skipping record. Could not determine username for record: " . $record->getDn());
        continue;
      }

      $user = User::find()->where(['username' => $username]);
      
      if(!empty($record->mail[0])) {
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

      Module::ldapRecordToUser($username, $record, $user);

      $this->fireEvent(self::EVENT_SYNC_USER, $user, $record);

      if (!$dryRun) {
        if($user->isModified() && !$user->save()) {
          echo "Error saving user: " . var_export($user->getValidationErrors(), true);
          continue;
        }

        go()->getDbConnection()
          ->replace('ldapauth_server_user_sync', ['serverId' => $id, 'userId' => $user->id])->execute();
      }      

			$this->output("Synced " . $username);		

			$usersInLDAP[] = $user->id;
		}

		if ($delete) {
			$this->deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun);
		}

    $this->output("Done\n\n");
  }

  private function getGOUserName(Record $record, Server $server) {
    $username = $record->{$server->usernameAttribute}[0] ?? null;

    if(!$username) {
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
    foreach($dn as $v) {
      if(substr($v, 0, 3) == 'dc=') {
        if($domain != "") {
          $domain .= '.';
        }
        $domain .= substr($v, 3);
      }
    }

    $mailDomain = isset($record->mail[0]) ? explode('@', $record->mail[0])[1] : null;

    if(empty($domain) || !in_array($domain, $this->domains)) {
	    go()->info("Using domain from mail property for " . $username);
	    if(empty($mailDomain)) {
		    go()->info("No email property available!");
		    return false;
      } 
      $domain = $mailDomain;
    }

    //fall back on the first if no domain was found from dn or mail address.
    if(!in_array($domain, $this->domains)) {
      $domain = $this->domains[0];
    }

    go()->debug("GO username should be: " . $username . '@' . $domain);

    return $username . '@' . $domain;

  }


  private function deleteUsers($usersInLDAP, $maxDeletePercentage, $dryRun) {
    $users = User::find()
      ->join('ldapauth_server_user_sync', 's', 's.userId = u.id')
      ->where('serverId', '=', $this->serverId)->execute();
		$totalInGO = $users->rowCount();
		$totalInLDAP = count($usersInLDAP);

		$this->output("Users in Group-Office: " . $totalInGO);
    $this->output("Users in LDAP: " . $totalInLDAP);

    $percentageToDelete = $totalInGO > 0 ? round((1 - $totalInLDAP / $totalInGO) * 100) : 0;		

    if($percentageToDelete > 0) {
      $this->output("Delete percentage: " . $percentageToDelete . "%\n");

      if ($percentageToDelete > $maxDeletePercentage)
        throw new \Exception("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the groups (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");

      foreach($users as $user) {
        if (!in_array($user->id, $usersInLDAP)) {
          $this->output("Deleting " . $user->username . "\n");
          if (!$dryRun)
            $user->delete();
        }
      }
    }
  }
  
  private $serverId;

  private function output($str) {
    go()->debug($str);

    echo $str . "\n";
  }

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

    $this->domains  = array_map(function($d) {return $d->name;}, $server->domains);

    if (!empty($server->username)) {
			if (!$connection->bind($server->username, $server->getPassword())) {				
				throw new \Exception("Invalid password given for '".$server->username."'");
			} else
			{
				go()->debug("Authenticated with user '" . $server->username . '"');
			}
		}

    $groupsInLDAP = [Group::ID_ADMINS, Group::ID_EVERYONE, Group::ID_INTERNAL];
		
		$records = Record::find($connection, $server->groupsDN, $server->syncGroupsQuery);
    
    $i = 0;
    foreach($records as $record) {
      $i++;
      $name = $record->cn[0];

      if (empty($name)) {
        throw new \Exception("Empty group name in LDAP record!");
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
        $user = \go\core\model\User::find(['id'])->where(['username' => $u['username']])->orWhere(['email' => $u['email']])->single();
        if (!$user) {
          $this->output("Error: user '" . $u['username'] . "' does not exist in Group-Office");
        } else {
          $this->output("Adding user '" . $u['username'] . "'");
          if(!in_array($user->id, $group->users)) {
	          $group->users[] = $user->id; //(new UserGroup())->setValue('userId', $user->id);
          }
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

			$this->output("Synced " . $name);		

			$groupsInLDAP[] = $group->id;
		}

		if ($delete) {
			$this->deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun);
		}

    $this->output("Done");
    
    // go()->getDebugger()->printEntries();

		//var_dump($attr);
  }

  private function deleteGroups($groupsInLDAP, $maxDeletePercentage, $dryRun) {
    $groups = Group::find()
      ->join('ldapauth_server_group_sync', 's', 's.groupId = g.id')
      ->where('serverId', '=', $this->serverId)->execute();

		$totalInGO = $groups->rowCount();
		$totalInLDAP = count($groupsInLDAP);

		$this->output("Groups in Group-Office: " . $totalInGO);
    $this->output("Groups in LDAP: " . $totalInLDAP);
    
    $percentageToDelete = $totalInGO > 0 ? round((1 - $totalInLDAP / $totalInGO) * 100) : 0;		

    if($percentageToDelete > 0) {
      $this->output("Delete percentage: " . $percentageToDelete . "%");

      if ($percentageToDelete > $maxDeletePercentage)
        throw new \Exception("Delete Aborted because script was about to delete more then $maxDeletePercentage% of the groups (" . $percentageToDelete . "%, " . ($totalInGO - $totalInLDAP) . " groups)\n");

      
      foreach($groups as $group) {
        if (!in_array($group->id, $groupsInLDAP)) {
          $this->output("Deleting " . $group->name);
          if (!$dryRun)
            $group->delete();
        }
      }
    }
  }
  
  private function getGroupMembers(Record $record, Connection $ldapConn, Server $server) {
    $members = [];
    if (isset($record->memberuid)) {
      //for openldap      
      foreach ($record->memberuid as $uid) {  
        $accountResult = Record::find($ldapConn, $server->peopleDN, 'uid=' . $uid);
        $record = $accountResult->fetch();
        
        $members[] = ['username' => $this->getGOUserName($record, $server), 'email' => $record->mail[0]];
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

	private function queryActiveDirectoryUser(Connection $ldapConn, $groupMember, Server $server) {
		$parts = preg_split('~(?<!\\\),~', $groupMember);
		$query = str_replace('\\,', ',', array_shift($parts));
		$query = str_replace('(', '\\(', $query);
		$query = str_replace(')', '\\)', $query);

		$searchDn = implode(',', $parts);

		$accountResult = Record::find($ldapConn, $searchDn, $query);
    $record = $accountResult->fetch();
    //Sometimes mail record doesn't exist. It can't find users by mail address in that case
		return ['username' => $this->getGOUserName($record, $server), 'email' => $record->mail[0] ?? null];
	}
}