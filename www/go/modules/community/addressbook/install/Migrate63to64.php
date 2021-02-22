<?php

namespace go\modules\community\addressbook\install;

use Exception;
use go\core\db\Database;
use go\core\db\Expression;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;
use function GO;
use go\core\db\Table;

/*

update addressbook_contact n set filesFolderId = (select files_folder_id from ab_contacts o where o.id=n.id) where n.filesFolderId = null ;
update addressbook_contact n set filesFolderId = (select files_folder_id from ab_companies o where o.id = (n.id - (select max(id) from ab_contacts)) ) where n.filesFolderId = null ;

update addressbook_contact n set registrationNumber = (select crn from ab_companies o where o.id = (n.id - (select max(id) from ab_contacts)) ) where n.registrationNumber = null ;


update comments_comment n set entityTypeId=(select id from core_entity where name='Contact'), entityId = (entityId + (select max(id) from ab_contacts)) where entityTypeId = 3;



update addressbook_address a inner join addressbook_contact c on c.id = a.contactId set a.type='visit' where a.type='home' and c.isOrganization = true;

update addressbook_contact c inner join ab_companies old on old.id + (select max(id) from ab_contacts) = c.id set c.name = concat(c.name, ' - ', old.name2) where old.name2 != "" and old.name2 is not null;
*/
class Migrate63to64 {
	
	private $countries;

	public function run() {
		
		//clear cache for ClassFinder fail in custom field type somehow.
		go()->getCache()->flush();
		Table::destroyInstances();

		Entity::$checkFilesFolder = false;

		// to speed things up
		Contact::$updateSearch = false;
		
		$this->countries = go()->t('countries');
		
		$db = go()->getDbConnection();
		//Start from scratch
		// $db->query("DELETE FROM addressbook_contact");
		// $db->query("DELETE FROM addressbook_addressbook");
		
			
		$this->migrateCustomFields();
		
		$this->migrateCompanyLinksAndComments();		

		$addressBooks = $db->select('a.*')->from('ab_addressbooks', 'a')
						->join("ab_contacts", 'c', 'c.addressbook_id = a.id', 'left')
						->join("ab_companies", 'o', 'o.addressbook_id = a.id', 'left')
						->groupBy(['a.id'])
						->having("count(c.id)>0 or count(o.id)>0")
						->all();

		$addressBooks[] = [
			'id' => 0,
			'user_id' => 1,
			'name' => '__ORPHANED__'
		];
		// echo $addressBooks ."\n";		

		foreach ($addressBooks as $abRecord) {
			echo "Migrating addressbook ". $abRecord['name'] . " (" .$abRecord['id'].")\n";
			flush();

			if(!empty($abRecord['id'])) {
				$addressBook = AddressBook::find()->where(['id' => $abRecord['id']])->single();
			} else {
				//only for __ORPHANED__
				$addressBook = AddressBook::find()->where(['name' => $abRecord['name']])->single();
			}

			if(!$addressBook) {
				$addressBook = new AddressBook();

				if(!empty($abRecord['id'])) {
					$addressBook->id = $abRecord['id'];
				}

				//make sure user ID exists
				$id = go()->getDbConnection()->selectSingleValue('id')->from('core_user')->where('id', '=', $abRecord['user_id'])->single();

				$addressBook->createdBy = $id ? $id : 1;

				//make sure ACL exists
				if(!empty($abRecord['acl_id'])) {
					$aclId = go()->getDbConnection()->selectSingleValue('id')->from('core_acl')->where('id', '=', $abRecord['acl_id'])->single();
					$addressBook->aclId = $aclId ? $aclId : null;
				}
				
				$addressBook->name = $abRecord['name'];
				$addressBook->filesFolderId = empty($abRecord['files_folder_id']) ? null : $abRecord['files_folder_id'];
				
				if (!$addressBook->save()) {
					throw new Exception("Could not save addressbook: " .var_export($addressBook->getValidationErrors(), true));
				}
			}

			$this->copyCompanies($addressBook, empty($abRecord['id']));
			
			$this->copyContacts($addressBook, empty($abRecord['id']));
			
			echo "\n";
			flush();
		}
		
		//$this->migrateCompanyLinks();		
		$this->addCustomFieldKeys();

		$m = new \go\core\install\MigrateCustomFields63to64();
		$m->migrateEntity("Contact");				
		
		$this->migrateCustomField();

    $this->checkCount();

    //remove orhpans if there were none.
		$addressBook = AddressBook::find()->where(['name'=> '__ORPHANED__'])->single();
		$orphanCount = go()->getDbConnection()
			->selectSingleValue('count(*)')
			->from('addressbook_contact')
			->where('addressBookId', '=', $addressBook->id)
			->single();
		if($orphanCount == 0) {
			AddressBook::delete(['id' => $addressBook->id]);
		}

		Entity::$checkFilesFolder = true;
		// to speed things up
		Contact::$updateSearch = true;
	}

  /**
   * Must be run on a copy. Result exported with insert ignore to be merged in live db,
   */
	public function fixMissing() {

	  //$sql = "select * from ab_companies where (id + (select max(id) from ab_contacts)) not in (select id from addressbook_contact) ORDER BY `ab_companies`.`name` ASC";
    foreach(AddressBook::find() as $addressBook) {
      $this->copyCompanies($addressBook);
    }

    /*
     *
     set sql_mode='';
      INSERT INTO addressbook_contact_custom_fields
 (id, col_1,  col_5, col_6, col_7, col_8, col_9, col_2, col_10)

 SELECT
 (model_id + (select max(id) from ab_contacts)) AS id,
  (select id from core_user where id = SUBSTRING_INDEX(col_1, ":", 1)) AS col_1,
   (select id from core_customfields_select_option where text = col_5 OR text = concat("** Missing ** ", col_5) LIMIT 0,1),
   (select id from core_customfields_select_option where text = col_6 OR text = concat("** Missing ** ", col_6) LIMIT 0,1),
   (select id from core_customfields_select_option where text = col_7 OR text = concat("** Missing ** ", col_7) LIMIT 0,1),
   col_8,
   (select id from core_customfields_select_option where text = col_9 OR text = concat("** Missing ** ", col_9) LIMIT 0,1),
    (select id from core_customfields_select_option where text = col_2 OR text = concat("** Missing ** ", col_2) LIMIT 0,1),
     (select id from core_customfields_select_option where text = col_10 OR text = concat("** Missing ** ", col_10) LIMIT 0,1)

     FROM `cf_ab_companies` WHERE (model_id + (select max(id) from ab_contacts)) not in (select id from addressbook_contact_custom_fields) and (model_id + (select max(id) from ab_contacts)) in (select id from addressbook_contact)


     */

  }

	private function checkCount() {
	  $c = go()->getDbConnection();
	  $oldContactCount = $c->selectSingleValue('count(*)')->from('ab_contacts')->single();
    $oldCompanyCount = $c->selectSingleValue('count(*)')->from('ab_companies')->single();
    $newCount = $c->selectSingleValue('count(*)')->from('addressbook_contact')->single();

    echo "Migrated " . $newCount ." contacts and organizations\n";

    if($oldContactCount + $oldCompanyCount != $newCount) {
      echo "Companies in old ab: " . $oldCompanyCount ."\n";
      echo "Companies in old ab: " . $oldContactCount ."\n";

      echo "Number of contacts is not equal to old contacts after migration. This might happen if there are some orphan contacts. You can identify them with:<br />
       <br />
      select * from ab_contacts where addressbook_id not in (select id from ab_addressbooks);<br />
      select * from ab_companies where addressbook_id not in (select id from ab_addressbooks);<br />
      <br />
      Perhaps you can simply delete them?";

      throw new \Exception("Number of contacts is not equal to old contacts after migration.");
    }
  }
	
	private function addCustomFieldKeys() {
		$c = go()->getDbConnection();
		$c->query("delete from addressbook_contact_custom_fields where id not in (select id from addressbook_contact);");	
		$c->query("ALTER TABLE `addressbook_contact_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `addressbook_contact`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;");

	}
	public function migrateCustomFields() {
		echo "Migrating custom fields\n";
		flush();
		$c = go()->getDbConnection();
		$c->query("DROP TABLE IF EXISTS addressbook_contact_custom_fields");
		$c->query("CREATE TABLE addressbook_contact_custom_fields LIKE cf_ab_contacts;");		
		$c->query("INSERT addressbook_contact_custom_fields SELECT * FROM cf_ab_contacts;");
		$c->query("ALTER TABLE `addressbook_contact_custom_fields` CHANGE `model_id` `id` INT(11) NOT NULL;");

		$delete = go()->getDbConnection()->delete('addressbook_contact_custom_fields', 'id not in (select id from ab_contacts)');		
		echo $delete ."\n";
		$delete->execute();		
		
		try{
			$this->mergeCompanyCustomFields();
		} catch(\Exception $e) {
			echo "WARNING: Will shrink column sizes because of error: " .$e->getMessage() ."\n";
			$this->shrinkToFit('addressbook_contact_custom_fields');
			$this->shrink = true;
			$this->mergeCompanyCustomFields();
		}
	}

	private $shrink = false;


	private function shrinkToFit($tableName) {
		$table = Table::getInstance($tableName);

		foreach($table->getColumns() as $c) {
			if($c->dbType == 'varchar' || $c->dbType == 'char') {
				$length = go()->getDbConnection()->selectSingleValue("max(length(`" . $c->name . "`))")->from($tableName)->single();

				if(!$length) {
					$length = 10;
				}

				$c->dataType = $c->dbType . '(' . $length . ')';
				$colDef = $c->getCreateSQL();

				$sql = "ALTER TABLE `" . $tableName . "` CHANGE `".$c->name."` `".$c->name."` " . $colDef;

				echo $sql ."\n";
				go()->getDbConnection()->exec($sql);
			}
		}
	}
	
	private function mergeCompanyCustomFields() {
		
		$companyTable = \go\core\db\Table::getInstance("cf_ab_companies");
		$cfTable = \go\core\db\Table::getInstance("addressbook_contact_custom_fields");
		$cols = $companyTable->getColumns();
		$cols = array_filter($cols, function($n) {return $n->name != "model_id";});
		
		if(empty($cols)) {
			return;
		}		
		
		$alterSQL = "ALTER TABLE addressbook_contact_custom_fields ";
		
		$renameMap = [];
		foreach($cols as $col) {
			$i = 1;
			$name = $stripped = preg_replace('/\s+/', '_', $col->name);
			while($cfTable->hasColumn($name)) {
				$name = $stripped . '_' . $i++;
			}
			$renameMap[$col->name] = $name;

			if($this->shrink && ($col->dbType == 'varchar' || $col->dbType == 'char')) {
				//prevent max row size error by shrinking column to fit
				$length = go()->getDbConnection()->selectSingleValue("max(length(`" . $col->name . "`))")->from("cf_ab_companies")->single();
				$col->dataType = $col->dbType . '(' . $length . ')';
			}
			
			$alterSQL .= 'ADD `' . $name . '` ' . $col->getCreateSQL() . ",\n";
		}
		
		$alterSQL = substr($alterSQL, 0, -2) . ';';
		
		echo $alterSQL."\n\n";
		
		go()->getDbConnection()->query($alterSQL);		
				
		$data = go()->getDbConnection()
						->select('(`model_id` + '. $this->getCompanyIdIncrement().') as id')
						->select(array_map([\go\core\db\Utils::class, "quoteColumnName"],array_keys($renameMap)), true)
						->from('cf_ab_companies', 'cf')
						->join('ab_companies', 'c', 'c.id=cf.model_id')
						->where('model_id in (select id from ab_companies)');

		
		
		$insert = go()->getDbConnection()->insertIgnore('addressbook_contact_custom_fields', $data, array_merge(['id'], array_values($renameMap)));
		echo $insert ."\n";
		$insert->execute();

		$companyEntityType =  (new Query)
			->select('*')
			->from('core_entity')
			->where('clientName = "Company"')
			->single();
		
		if($companyEntityType) {
			
			foreach($renameMap as $old => $new) {
				go()->getDbConnection()
							->update("core_customfields_field", 
											['databaseName' => $new],
											(new \go\core\orm\Query)
											->where(	'fieldSetId', 'IN', 
															(new \go\core\db\Query)
																->select('id')
																->from('core_customfields_field_set')
																->where(['entityId' => $companyEntityType['id']])
															)
											->andWhere('databaseName', '=', $old)
											)
							->execute();
			}
			
			go()->getDbConnection()
							->update("core_customfields_field_set", 
											['entityId' => Contact::entityType()->getId()], 
											['entityId' => $companyEntityType['id']])
							->execute();
		}
		
		\go\core\db\Table::destroyInstances();
	}
	
	public function migrateCompanyLinksAndComments() {

		echo "Migrating links\n";
		flush();
		$companyEntityType =  (new Query)
						->select('*')
						->from('core_entity')
						->where('clientName = "Company"')
						->single();

		if(!$companyEntityType) {
			return;
		}
		
		go()->getDbConnection()->beginTransaction();
		go()->getDbConnection()
						->update("core_link",
										[
												'fromEntityTypeId' => Contact::entityType()->getId(),
												'fromId' => new \go\core\db\Expression('fromId + ' . $this->getCompanyIdIncrement())
										],
										['fromEntityTypeId' => $companyEntityType['id']])
						->execute();

		go()->getDbConnection()
						->update("core_link",
										[
												'toEntityTypeId' => Contact::entityType()->getId(),
												'toId' => new \go\core\db\Expression('toId + ' . $this->getCompanyIdIncrement())
										],
										['toEntityTypeId' => $companyEntityType['id']])
						->execute();


		if(\go\core\model\Module::isInstalled( 'community', 'comments') || \go\core\model\Module::isInstalled( 'legacy', 'comments')) {
			go()->getDbConnection()->exec("update comments_comment n set entityTypeId=(select id from core_entity where clientName='Contact'), entityId = (entityId + (select max(id) from ab_contacts)) where entityTypeId = (select id from core_entity where clientName='Company');");
		}

		go()->getDbConnection()
			->update("core_search",
				[
					'entityTypeId' => Contact::entityType()->getId(),
					'entityId' => new \go\core\db\Expression('entityId + ' . $this->getCompanyIdIncrement())
				],
				['entityTypeId' => $companyEntityType['id']])
			->execute();


		go()->getDbConnection()->delete("core_entity", ['clientName' => "Company"])->execute();

		go()->getDbConnection()->commit();

	}
	
	public function migrateCustomField() {
		
		echo "Migrating address book custom field types\n";
		flush();
	
		$cfMigrator = new \go\core\install\MigrateCustomFields63to64();
		$fields = \go\core\model\Field::find()->where(['type' => [
				'Contact', 
				'Company'
				]]);
		
		foreach($fields as $field) {
			try{
				echo "Migrating ".$field->databaseName ."\n";
				if($field->type == "Company") {
					$field->type = "Contact";
					$field->setOption("isOrganization", true);
					$incrementID = $this->getCompanyIdIncrement();
				} else
				{
					$field->setOption("isOrganization", false);
					$incrementID = 0;
				}
				$cfMigrator->updateSelectEntity($field, Contact::class, $incrementID);
			} catch(\Exception $e) {
				echo "ERROR: Failed to migrate ".$field->databaseName .' - '. $field->id."\n";
			}
		}
	}
	
	private $companyIdIncrement;
	
	public function getCompanyIdIncrement() {
		if(!isset($this->companyIdIncrement)) {
			$this->companyIdIncrement = (int) go()->getDbConnection()->selectSingleValue('max(id)')->from('ab_contacts')->execute()->fetch();
		}
		return $this->companyIdIncrement;
	}





	private function copyContacts(AddressBook $addressBook, $orphans = false) {

		
		$db = go()->getDbConnection();

		$contacts = $db->select()->from('ab_contacts')
						->andWhere('id not in (select id from addressbook_contact)')
						->orderBy(['id' => 'ASC']);

		if(!$orphans) {
			$contacts->where(['addressbook_id' => $addressBook->id]);
		}else{
			$contacts->where('addressbook_id NOT IN (select id from ab_addressbooks)');
		}

		//continue where we left last time if failed.
//		$max = $db->selectSingleValue('max(id)')
//						->from("addressbook_contact")
//						->where('id', '<', $this->getCompanyIdIncrement())
//						->andWhere(['addressBookId' => $addressBook->id])
//						->single();
//
//		if($max>0) {
//			$contacts->andWhere('id', '>', $max);
//		}
						

		$count = 0;
		foreach ($contacts as $r) {
			$r = array_map("trim", $r);
			echo ".";			

			$count++;
			if($count == 50) {
				echo "\n";
				$count = 0;
			}
			flush();
		
			$contact = new Contact();
			$contact->id = $r['id'];
			$contact->addressBookId = $addressBook->id;
			$contact->initials = $r['initials'];
			$contact->firstName = $r['first_name'];
			$contact->middleName = $r['middle_name'];
			$contact->lastName = $r['last_name'];

			$contact->prefixes = $r['title'];
			$contact->suffixes = $r['suffix'];
			$contact->gender = $r['sex'];

			if (!empty($r['birthday'])) {
				$contact->dates[] = (new Date())
								->setValues([
						'type' => Date::TYPE_BIRTHDAY,
						'date' => DateTime::createFromFormat('Y-m-d', $r['birthday'])
				]);
			}

			if ($r['action_date'] > 0) {
				$contact->dates[] = (new Date())
								->setValues([
						'type' => "action",
						'date' => DateTime::createFromFormat('U', $r['action_date'])
				]);
			}

			if (!empty($r['email'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email']
				]);
			}

			if (!empty($r['email2'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email2']
				]);
			}
			if (!empty($r['email3'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email3']
				]);
			}


			//$r['department'] ???

			$contact->jobTitle = $r['function'];

			if (!empty($r['home_phone'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_HOME,
						'number' => $r['home_phone']
				]);
			}

			if (!empty($r['work_phone'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_WORK,
						'number' => $r['work_phone']
				]);
			}

			if (!empty($r['fax'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_FAX,
						'number' => $r['fax']
				]);
			}

			if (!empty($r['work_fax'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_FAX,
						'number' => $r['work_fax']
				]);
			}

			if (!empty($r['cellular'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_MOBILE,
						'number' => $r['cellular']
				]);
			}

			if (!empty($r['cellular2'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_MOBILE,
						'number' => $r['cellular2']
				]);
			}

			if (!empty($r['homepage'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_HOMEPAGE,
						'url' => $r['homepage']
				]);
			}

			if (!empty($r['url_facebook'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_FACEBOOK,
						'url' => $r['url_facebook']
				]);
			}

			if (!empty($r['url_linkedin'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_LINKEDIN,
						'url' => $r['url_linkedin']
				]);
			}

			if (!empty($r['url_twitter'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_TWITTER,
						'url' => $r['url_twitter']
				]);
			}

			if (!empty($r['skype_name'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => "skype",
						'url' => $r['skype_name']
				]);
			}


			$address = new Address();
			if(!empty($r['country']) && \go\core\validate\CountryCode::validate(strtoupper($r['country'])))
				$address->countryCode = strtoupper($r['country']);

			if(!empty($r['state']))
				$address->state = $r['state'];

			if(!empty($r['city']))
				$address->city = $r['city'];

			if(!empty($r['zip']))
				$address->zipCode = $r['zip'];

			if(!empty($r['address']))
				$address->street = $r['address'];

			if(!empty($r['address_no']))
				$address->street2 = $r['address_no'];

			if(!empty($r['latitude']))
				$address->latitude = $r['latitude'];

			if(!empty($r['longitude']))
				$address->longitude = $r['longitude'];

			if ($address->isModified()) {
				$address->type = Address::TYPE_HOME;
				$address->cutPropertiesToColumnLength();
				$contact->addresses[] = $address;
			}

			$contact->notes = $r['comment'];

			$contact->filesFolderId = empty($r['files_folder_id']) ? null : $r['files_folder_id'];

			$contact->createdAt = new DateTime("@" . $r['ctime']);
			$contact->modifiedAt = new DateTime("@" . $r['mtime']);
			$contact->createdBy = \go\core\model\User::exists($r['user_id']) ? $r['user_id'] : 1;
			$contact->modifiedBy = \go\core\model\User::exists($r['muser_id']) ? $r['muser_id'] : 1;
			$contact->goUserId = empty($r['go_user_id']) || !\go\core\model\User::findById($r['go_user_id'], ['id']) || Contact::findForUser($r['go_user_id'], ['id']) ? null : $r['go_user_id'];

			if ($r['photo']) {

				$file = go()->getDataFolder()->getFile($r['photo']);
				if ($file->exists()) {
					$tmpFile = \go\core\fs\File::tempFile($file->getExtension());
					$file->copy($tmpFile);
					$blob = \go\core\fs\Blob::fromTmp($tmpFile);
					if (!$blob->save()) {
						throw new \Exception("Could not save blob");
					}

					$contact->photoBlobId = $blob->id;
				}
			}

			$contact->cutPropertiesToColumnLength();
			if (!$contact->save()) {
				go()->debug($r);
				throw new \Exception("Could not save contact" . var_export($contact->getValidationErrors(), true));
			}
			
			if($r['company_id']) {				
				$orgId = $r['company_id'] + $this->getCompanyIdIncrement();
				
				$org = Contact::findById($orgId);
				if($org) {
					\go\core\model\Link::create($contact, $org);
				}
			}
		}
	}
//select * from ab_companies where (id + (select max(id) from ab_contacts)) not in (select id from addressbook_contact)
	private function copyCompanies(AddressBook $addressBook, $orphans = false) {
		$db = go()->getDbConnection();		

		$contacts = $db->select()
		->from('ab_companies')
		->andWhere('(id + '.$this->getCompanyIdIncrement().') not in (select id from addressbook_contact)');

		if(!$orphans) {
			$contacts->where(['addressbook_id' => $addressBook->id]);
		}else{
			$contacts->where('addressbook_id NOT IN (select id from ab_addressbooks)');
		}


		//continue where we left last time if failed.
//		$max = $db->selectSingleValue('max(id)')->from("addressbook_contact")->andWhere(['addressBookId' => $addressBook->id])->single();
//		if($max>0) {
//			$contacts->andWhere('id', '>', $max - $this->getCompanyIdIncrement());
//		}

		$count = 0;

		foreach ($contacts as $r) {
			$r = array_map("trim", $r);

			echo ".";

			$count++;
			if($count == 50) {
				echo "\n";
				$count = 0;
			}
			flush();

			$contact = new Contact();
			$contact->isOrganization = true;
			$contact->id = $r['id'] + $this->getCompanyIdIncrement();
			$contact->addressBookId = $addressBook->id;
			$contact->name = $r['name'];

			if(!empty($r['name2'])) {
				$contact->name .= ' - ' . $r['name2'];
			}

			if (!empty($r['email'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email']
				]);
			}

			if (!empty($r['invoice_email'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_BILLING,
						'email' => $r['invoice_email']
				]);
			}


			if (!empty($r['phone'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_WORK,
						'number' => $r['phone']
				]);
			}

			if (!empty($r['fax'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_FAX,
						'number' => $r['fax']
				]);
			}


			if (!empty($r['homepage'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_HOMEPAGE,
						'url' => $r['homepage']
				]);
			}



			$address = new Address();

			if(!empty($r['country']) && \go\core\validate\CountryCode::validate(strtoupper($r['country'])))
				$address->countryCode = strtoupper($r['country']);

			if(!empty($r['state']))
				$address->state = $r['state'];

			if(!empty($r['city']))
				$address->city = $r['city'];

			if(!empty($r['zip']))
				$address->zipCode = $r['zip'];

			if(!empty($r['address']))
				$address->street = $r['address'];

			if(!empty($r['address_no']))
				$address->street2 = $r['address_no'];

			if(!empty($r['latitude']))
				$address->latitude = $r['latitude'];

			if(!empty($r['longitude']))
				$address->longitude = $r['longitude'];

			if ($address->isModified()) {
				$address->cutPropertiesToColumnLength();
				$address->type = Address::TYPE_VISIT;
				$contact->addresses[] = $address;
			}

			$address = new Address();

			if(!empty($r['country']) && \go\core\validate\CountryCode::validate(strtoupper($r['post_country'])))
				$address->countryCode = strtoupper($r['post_country']);

			if(!empty($r['post_state']))
				$address->state = $r['post_state'];

			if(!empty($r['post_city']))
				$address->city = $r['post_city'];

			if(!empty($r['post_zip']))
				$address->zipCode = $r['post_zip'];

			if(!empty($r['post_address']))
				$address->street = $r['post_address'];

			if(!empty($r['post_address_no']))
				$address->street2 = $r['post_address_no'];

			if(!empty($r['post_latitude']))
				$address->latitude = $r['post_latitude'];

			if(!empty($r['post_longitude']))
				$address->longitude = $r['post_longitude'];

			if ($address->isModified()) {
				$address->cutPropertiesToColumnLength();
				$address->type = Address::TYPE_POSTAL;
				$contact->addresses[] = $address;
			}

			$contact->notes = $r['comment'];

			$contact->filesFolderId = $contact->filesFolderId = empty($r['files_folder_id']) ? null : $r['files_folder_id'];

			$contact->createdAt = new DateTime("@" . $r['ctime']);
			$contact->modifiedAt = new DateTime("@" . $r['mtime']);
			$contact->createdBy = \go\core\model\User::exists($r['user_id']) ? $r['user_id'] : 1;
			$contact->modifiedBy = \go\core\model\User::exists($r['muser_id']) ? $r['muser_id'] : 1;

			$contact->IBAN = $r['iban'];
			$contact->BIC = $r['bank_bic'];
			$contact->registrationNumber = $r['crn'];

			$contact->vatNo = $r['vat_no'];

			if ($r['photo']) {

				$file = go()->getDataFolder()->getFile($r['photo']);
				if ($file->exists()) {
					$tmpFile = \go\core\fs\File::tempFile($file->getExtension());
					$file->copy($tmpFile);
					$blob = \go\core\fs\Blob::fromTmp($tmpFile);
					if (!$blob->save()) {
						throw new \Exception("Could not save blob");
					}

					$contact->photoBlobId = $blob->id;
				}
			}

			$contact->cutPropertiesToColumnLength();

			if (!$contact->save()) {

				go()->debug($r);

				throw new \Exception("Could not save contact" . var_export($contact->getValidationErrors(), true));
			}
		}
	}
	
	public function addInitials() {

		if(!go()->getDatabase()->hasTable('ab_contacts')) {
			return;
		}

		$stmt = go()->getDbConnection()
			->update("addressbook_contact", 
				[
					"initials" => new Expression('old.initials')
				],
					(new Query)
						->join('ab_contacts','old','old.id = t.id')
				);
				
		echo $stmt . "\n";
		$stmt->execute();
	}

	public function addDepartment() {

		if(!go()->getDatabase()->hasTable('ab_contacts')) {
			return;
		}

		$stmt = go()->getDbConnection()
			->update("addressbook_contact",
				[
					"department" => new Expression('old.department')
				],
				(new Query)
					->join('ab_contacts','old','old.id = t.id')
			);
		echo $stmt . "\n";
		$stmt->execute();
	}

	public function addColor() {

		if(!go()->getDatabase()->hasTable('ab_contacts')) {
			return;
		}

		$stmt = go()->getDbConnection()
			->update("addressbook_contact",
				[
					"color" => new Expression('old.color')
				],
				(new Query)
					->join('ab_contacts','old','old.color != "000000" AND old.id = t.id')
			);
		echo $stmt . "\n";
		$stmt->execute();

		$stmt = go()->getDbConnection()
			->update("addressbook_contact",
				[
					"color" => new Expression('old.color')
				],
				(new Query)
					->join('ab_companies','old','old.color != "000000" AND old.id = (t.id - ' . $this->getCompanyIdIncrement() .')')

			);
		echo $stmt . "\n";
		$stmt->execute();
	}

	public function addSalutation() {

		if(!go()->getDatabase()->hasTable('ab_contacts')) {
			return;
		}

		$stmt = go()->getDbConnection()
			->update("addressbook_contact", 
				[
					"salutation" => new Expression('old.salutation')
				],
					(new Query)
						->join('ab_contacts','old','old.id = t.id')

				);		
		echo $stmt . "\n";
		$stmt->execute();
	}

}

