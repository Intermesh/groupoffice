<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: addressbook.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

class addressbook extends db {




	function is_duplicate_contact($contact) {
		$contact = $contact;

		$contact['email']=isset($contact['email']) ? $contact['email'] : '';
		$contact['first_name']=isset($contact['first_name']) ? $contact['first_name'] : '';
		$contact['middle_name']=isset($contact['middle_name']) ? $contact['middle_name'] : '';
		$contact['last_name']=isset($contact['last_name']) ? $contact['last_name'] : '';

		$sql = "SELECT id FROM ab_contacts WHERE ".
				"addressbook_id='".$this->escape($contact['addressbook_id'])."' AND ".
				"first_name='".$this->escape($contact['first_name'])."' AND ".
				"middle_name='".$this->escape($contact['middle_name'])."' AND ".
				"last_name='".$this->escape($contact['last_name'])."' AND ".
				"email='".$this->escape($contact['email'])."'";

		$this->query($sql);
		if($this->next_record()) {
			return $this->f('id');
		}
		return false;
	}

	function parse_address($address) {
		$address = trim($address);

		$address_arr['housenumber'] = '';
		$address_arr['street'] = $address;

		if ($address != '') {
			$last_space = strrpos($address, ' ');

			if ($last_space !== false) {
				$address_arr['housenumber'] = substr($address, $last_space +1);
				$address_arr['street'] = substr($address, 0, $last_space);

			}
		}
		return $address_arr;
	}

	public function save_contact_photo($tmp_file, $contact_id){
		global $GO_CONFIG, $GO_MODULES, $GO_LANGUAGE, $lang;

		$destination = $GLOBALS['GO_CONFIG']->file_storage_path.'contacts/contact_photos/'.$contact_id.'.jpg';

		File::mkdir(dirname($destination));

		$img = new Image();
		if(!$img->load($tmp_file)){
			$GLOBALS['GO_LANGUAGE']->require_language_file('addressbook');
			throw new Exception($lang['addressbook']['imageNotSupported']);
		}

		$img->zoomcrop(90,120);
		$img->save($destination, IMAGETYPE_JPEG);

		return $GLOBALS['GO_MODULES']->modules['addressbook']['url'].'photo.php?contact_id='.$contact_id;
	}

	public static function add_user($user) {
		$ab = new addressbook();
		$ab->create_default_addressbook($user);
	}

	function create_default_addressbook($user) {
		global $GO_CONFIG;
		
		$tpl = $GLOBALS['GO_CONFIG']->get_setting('task_name_template');

		if(!$tpl)
			$tpl = '{first_name} {middle_name} {last_name}';

			$name = String::reformat_name_template($tpl,$user);

		//$name = String::format_name($user, '','','last_name');
		$new_ab_name = $name;
		$x = 1;
		while ($this->get_addressbook_by_name($new_ab_name)) {
			$new_ab_name = $name.' ('.$x.')';
			$x ++;
		}
		$addressbook = $this->add_addressbook($user['id'], $new_ab_name);
		$addressbook=$addressbook['id'];
		return $addressbook;
	}

	function get_addressbook($addressbook_id=0, $user_addressbook=false) {
		if($addressbook_id == 0) {
			global $GO_SECURITY;

			if($user_addressbook) {
				$sql = "SELECT * FROM ab_addressbooks WHERE user_id=".$GLOBALS['GO_SECURITY']->user_id." ORDER BY id ASC";
				$this->query($sql);
			}else {
				$this->get_writable_addressbooks($GLOBALS['GO_SECURITY']->user_id);
			}

			if($this->next_record()) {
				$addressbook_id = $this->f('id');
			}else {
				global $GO_CONFIG;
				require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
				$GO_USERS = new GO_USERS();

				$user = $GO_USERS->get_user($GLOBALS['GO_SECURITY']->user_id);
				$addressbook = $this->create_default_addressbook($user);
				$addressbook_id=$addressbook['id'];
			}
		}
		$sql = "SELECT * FROM ab_addressbooks WHERE id='".$this->escape($addressbook_id)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->record;
		}else {
			return false;
		}
	}

	function get_user_addressbooks($user_id, $start=0, $offset=0, $sort='name', $dir='ASC', $query='') {
		$sql = "SELECT ab_addressbooks.* ".
				"FROM ab_addressbooks ".

		"INNER JOIN go_acl a ON (ab_addressbooks.acl_id = a.acl_id".
		" AND (a.user_id=".intval($user_id)." OR a.group_id IN (".implode(',',$GLOBALS['GO_SECURITY']->get_user_group_ids($user_id))."))) ";


		if(!empty($query))
 		{
 			$sql .= " WHERE name LIKE '".$this->escape($query)."'";
 		}

		$sql .=	" GROUP BY ab_addressbooks.id ORDER BY ab_addressbooks.".$sort." ".$dir;

		$sql = $this->add_limits_to_query($sql, $start, $offset);
		$this->query($sql);

		return $this->limit_count();
	}

	function get_contacts_for_export($addressbook_id, $user_id = 0) {
		global $GO_SECURITY;

		if ($user_id == 0) {
			$user_id = $GLOBALS['GO_SECURITY']->user_id;
		}
		$sql = "SELECT ab_contacts.*,".
				"ab_companies.name AS company FROM ab_contacts ".
				"LEFT JOIN ab_companies ON (ab_contacts.company_id=ab_companies.id) ".
				" WHERE ab_contacts.addressbook_id='".$this->escape($addressbook_id)."' ".
				" ORDER BY ab_contacts.first_name, ab_contacts.last_name ASC";

		$this->query($sql);
		return $this->num_rows();
	}

	function get_contacts($addressbook_id=0, $sort = "name", $direction = "ASC", $start=0, $offset=0) {
		global $GO_SECURITY;

		if ($sort == 'name') {
			if ($_SESSION['GO_SESSION']['sort_name'] == 'first_name') {
				$sort = 'first_name '.$direction.', last_name';
			} else {
				$sort = 'last_name '.$direction.', first_name';
			}
		}
		$sql = "SELECT * FROM ab_contacts ";
		if($addressbook_id>0) {
			$sql .= " WHERE ab_contacts.addressbook_id='".$this->escape($addressbook_id)."'";
		}

		$sql .= 	" ORDER BY ".$this->escape($sort.' '.$direction);

		$this->query($sql);
		$count =  $this->num_rows();
		if ($offset != 0 && $count > $offset) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
			$this->query($sql);
		}

		return $count;
	}

	function get_user_addressbook_ids($user_id) {
	/*if(!isset($_SESSION['GO_SESSION'][$user_id]['authorized_addressbooks'])) {
		$_SESSION['GO_SESSION'][$user_id]['authorized_addressbooks'] = array();
		$this->get_user_addressbooks($user_id);
		while($this->next_record()) {
			$_SESSION['GO_SESSION'][$user_id]['authorized_addressbooks'][] = $this->f('id');
		}
	}
	return $_SESSION['GO_SESSION'][$user_id]['authorized_addressbooks'];*/

		$addressbooks=array();
		$this->get_user_addressbooks($user_id);
		while($this->next_record()) {
			$addressbooks[] = $this->f('id');
		}

		return $addressbooks;
	}

	function get_writable_addressbooks($user_id, $start=0, $offset=0, $sort='name', $dir='ASC', $query='') {
		$sql = "SELECT ab_addressbooks.* ".
				"FROM ab_addressbooks ".

		"INNER JOIN go_acl a ON (ab_addressbooks.acl_id = a.acl_id";
		$sql .= " AND a.level>".GO_SECURITY::READ_PERMISSION;
		$sql .= " AND (a.user_id=".intval($user_id)." OR a.group_id IN (".implode(',',$GLOBALS['GO_SECURITY']->get_user_group_ids($user_id))."))) ";

		if(!empty($query)){
			$sql .= "WHERE ab_addressbooks.name LIKE '".$this->escape($query)."'";
		}

		$sql .= " GROUP BY ab_addressbooks.id ORDER BY ab_addressbooks.".$sort." ".$dir;

		$sql = $this->add_limits_to_query($sql, $start, $offset);
		$this->query($sql);

		return $this->limit_count();
	}

	function add_company($company, $addressbook=false) {

		if (!isset($company['user_id']) || $company['user_id'] == 0) {
			global $GO_SECURITY;
			$company['user_id'] = $GLOBALS['GO_SECURITY']->user_id;
		}

		if (!isset($company['ctime']) || $company['ctime'] == 0) {
			$company['ctime'] = time();
		}
		if (!isset($company['mtime']) || $company['mtime'] == 0) {
			$company['mtime'] = $company['ctime'];
		}

//		if(!isset($company['iso_address_format'])){
//			if(!$addressbook) {
//				$addressbook = $this->get_addressbook($company['addressbook_id']);
//			}
//			$company['iso_address_format']=$addressbook['default_iso_address_format'];
//		}
//		if(!isset($company['post_iso_address_format'])){
//			if(!$addressbook) {
//				$addressbook = $this->get_addressbook($company['addressbook_id']);
//			}
//			$company['post_iso_address_format']=$addressbook['default_iso_address_format'];
//		}

		global $GO_MODULES;
		if(!isset($company['files_folder_id']) && isset($GLOBALS['GO_MODULES']->modules['files'])) {
			global $GO_CONFIG;

			if(!$addressbook) {
				$addressbook = $this->get_addressbook($company['addressbook_id']);
			}
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();

			$new_path = $this->build_company_files_path($company, $addressbook);
			if($folder=$files->create_unique_folder($new_path)) {
				$company['files_folder_id']=$folder['id'];
			}
		}

		$company['id'] = $this->nextid("ab_companies");
		$this->insert_row('ab_companies', $company);
		$this->cache_company($company['id']);

		return $company['id'];
	}

	function update_company($company, $addressbook=false, $old_company=false) {

		if (!isset($company['mtime']) || $company['mtime'] == 0) {
			$company['mtime'] = time();
		}

		if(!$old_company) {
			$old_company = $this->get_company($company['id']);
		}

		global $GO_MODULES;

		if(isset($GLOBALS['GO_MODULES']->modules['files']) && isset($company['addressbook_id'])) {
			if(!$addressbook) {
				$addressbook = $this->get_addressbook($company['addressbook_id']);
			}
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();



			$new_path = $this->build_company_files_path($company, $addressbook);
			$company['files_folder_id']=$files->check_folder_location($old_company['files_folder_id'], $new_path);
		}

		$r = $this->update_row('ab_companies', 'id', $company);

		if(isset($company['addressbook_id']) && $old_company['addressbook_id'] != $company['addressbook_id']) {
			$this->move_contacts_company($company['id'], $old_company['addressbook_id'], $company['addressbook_id']);
		}

		$this->cache_company($company['id']);
		return $r;
	}

	function get_companies($addressbook_id=0, $sort = 'name', $direction = 'ASC', $start = 0, $offset = 0) {
		global $GO_SECURITY;

		$sql = "SELECT ab_companies.* FROM ab_companies";

		if($addressbook_id > 0) {
			$sql .= " WHERE addressbook_id='$addressbook_id'";
		}

		$sql .= " ORDER BY ".$this->escape($sort.' '.$direction);
		$this->query($sql);
		$count = $this->num_rows();

		if ($offset != 0 && $count > $offset) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
			$this->query($sql);
		}
		return $count;
	}

	function get_company($company_id) {
		$sql = "SELECT ab_companies.*, ab_addressbooks.acl_id ".
				"FROM ab_companies ".
				"INNER JOIN ab_addressbooks ON (ab_addressbooks.id=ab_companies.addressbook_id) ".
				"WHERE ab_companies.id='".$this->escape($company_id)."'";
		$this->query($sql);
		return $this->next_record(DB_ASSOC);
	}

	function get_company_by_name($addressbook_id, $name) {
		$sql = "SELECT * FROM ab_companies WHERE addressbook_id='".$this->escape($addressbook_id)."' AND name='".$this->escape($name)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->record;
		}
		return false;
	}

	function get_company_by_email($email, $user_id) {
		$sql = "SELECT * FROM ab_companies WHERE ";

		$user_ab = $this->get_user_addressbook_ids($user_id);
		if(count($user_ab) > 1) {
			$sql .= "addressbook_id IN (".implode(",",$user_ab).") ";
		}elseif(count($user_ab)==1) {
			$sql .= "addressbook_id=".$user_ab[0]." ";
		}else
		{
			return false;
		}
		$sql .= "AND email='".$this->escape($email)."' LIMIT 0,1";
		
		$this->query($sql);
		if ($this->next_record()) {
			return $this->record;
		}
		return false;
	}

	function get_company_id_by_name($name, $addressbook_id) {
		$sql = "SELECT id FROM ab_companies WHERE addressbook_id='$addressbook_id' AND name='".$this->escape($name)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->f('id');
		}
		return false;
	}

	function get_company_contacts($company_id, $sort = "name", $direction = "ASC", $start=0, $offset=0) {
		if ($sort == 'name') {
			if ($_SESSION['GO_SESSION']['sort_name'] == 'first_name') {
				$sort = 'first_name '.$direction.', last_name';
			} else {
				$sort = 'last_name '.$direction.', first_name';
			}

		//	  $sort = 'first_name '.$direction.', last_name';
		}
		$sql = "SELECT * FROM ab_contacts WHERE company_id='".$this->escape($company_id)."' ORDER BY ".$this->escape($sort.' '.$direction);

		if ($offset != 0) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);

			$sql2 = "SELECT * FROM ab_contacts WHERE company_id='".$this->escape($company_id)."'";

			$this->query($sql2);
			$count = $this->num_rows();

			if ($count > 0) {
				$this->query($sql);
				return $count;
			}
			return 0;

		} else {
			$this->query($sql);
			return $this->num_rows();
		}
	}

	function delete_company($company_id, $company=false) {
		
		return GO_Addressbook_Model_Company::model()->findByPk($company_id)->delete();
		
//		global $GO_CONFIG, $GO_MODULES;
//
//		if(isset($GLOBALS['GO_MODULES']->modules['files'])) {
//			$company=$company ? $company : $this->get_company($company_id);
//			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
//			$files = new files();
//			try {
//				$files->delete_folder($company['files_folder_id']);
//			}catch(Exception $e ){}
//		}
//
//		$sql = "UPDATE ab_contacts SET company_id=0 WHERE company_id=$company_id";
//		$this->query($sql);
//
//		require_once($GLOBALS['GO_CONFIG']->class_path.'base/search.class.inc.php');
//		$search = new search();
//		$search->delete_search_result($company_id, 3);
//
//		$sql = "DELETE FROM ab_companies WHERE id='".$this->escape($company_id)."'";
//		if ($this->query($sql)) {
//			return true;
//		}
	}

	function add_contact(&$contact, $addressbook=false) {

		global $GO_MODULES;

		if (!isset($contact['user_id']) || $contact['user_id'] == 0) {
			global $GO_SECURITY;
			$contact['user_id'] = $GLOBALS['GO_SECURITY']->user_id;
		}

		if (!isset($contact['ctime']) || $contact['ctime'] == 0) {
			$contact['ctime'] = time();
		}
		if (!isset($contact['mtime']) || $contact['mtime'] == 0) {
			$contact['mtime'] = $contact['ctime'];
		}

		if (isset($contact['sex']) && $contact['sex'] == '') {
			$contact['sex'] = 'M';
		}

		if(empty($contact['first_name']) && empty($contact['last_name'])){
			$contact['first_name']='Unnamed';
		}

//		if(!isset($contact['iso_address_format'])){
//			if(!$addressbook) {
//				$addressbook = $this->get_addressbook($contact['addressbook_id']);
//			}
//			$contact['iso_address_format']=$addressbook['default_iso_address_format'];
//		}

		if(!isset($contact['email_allowed']))
			$contact['email_allowed']='1';

		if(!isset($contact['files_folder_id']) && isset($GLOBALS['GO_MODULES']->modules['files'])) {
			global $GO_CONFIG;

			if(!$addressbook) {
				$addressbook = $this->get_addressbook($contact['addressbook_id']);
			}
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();

			$new_path = $this->build_contact_files_path($contact, $addressbook);
			if($folder=$files->create_unique_folder($new_path)) {
				$contact['files_folder_id']=$folder['id'];
			}
		}

		$contact['id'] = $this->nextid("ab_contacts");
		$this->insert_row('ab_contacts', $contact);
		$this->cache_contact($contact['id']);
		return $contact['id'];
	}

	function build_contact_files_path($contact, $addressbook) {
		$new_folder_name = File::strip_invalid_chars(String::format_name($contact));
		$last_part = empty($contact['last_name']) ? '' : $this->get_index_char($contact['last_name']);
		$new_path = 'contacts/'.File::strip_invalid_chars($addressbook['name']);
		if(!empty($last_part)) {
			$new_path .= '/'.$last_part;
		}else {
			$new_path .= '/0 no last name';
		}
		
		if(empty($new_folder_name))
			$new_folder_name='unnamed';
			
		$new_path .= '/'.$new_folder_name;
		return $new_path;
	}

	function build_company_files_path($company, $addressbook) {
		$new_folder_name = File::strip_invalid_chars($company['name']);
		$last_part = $this->get_index_char($company['name']);
		$new_path = 'companies/'.File::strip_invalid_chars($addressbook['name']);
		if(!empty($last_part)) {
			$new_path .= '/'.$last_part;
		}
		$new_path .= '/'.$new_folder_name;
		
		if(empty($new_folder_name))
			$new_folder_name='unnamed';
		
		return $new_path;
	}

	function update_contact($contact, $addressbook=false, $old_contact=false) {
		if (!isset($contact['mtime']) || $contact['mtime'] == 0) {
			$contact['mtime'] = time();
		}

		if (isset($contact['sex']) && $contact['sex'] == '') {
			$contact['sex'] = 'M';
		}

		
		if(!$old_contact) {
			$old_contact = $this->get_contact($contact['id']);
		}


		/*if(empty($contact['first_name']) && empty($contact['last_name']) && empty($old_contact['first_name']) && empty($old_contact['last_name'])){
			$contact['first_name']='Unnamed';
		}*/


		global $GO_MODULES;
		if(isset($GLOBALS['GO_MODULES']->modules['files']) && isset($contact['addressbook_id'])) {
			if(!$addressbook) {
				$addressbook = $this->get_addressbook($contact['addressbook_id']);
			}
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();

			if(!isset($contact['last_name']))
				$contact['last_name']=$old_contact['last_name'];
			if(!isset($contact['first_name']))
				$contact['first_name']=$old_contact['first_name'];
			if(!isset($contact['middle_name']))
				$contact['middle_name']=$old_contact['middle_name'];

			$new_path = $this->build_contact_files_path($contact, $addressbook);
			$contact['files_folder_id']=$files->check_folder_location($old_contact['files_folder_id'], $new_path);
		}

		$r = $this->update_row('ab_contacts', 'id', $contact);

		if(isset($contact['addressbook_id']) && $old_contact['addressbook_id']!=$contact['addressbook_id']) {
			$this->move_contacts_company($contact['company_id'], $old_contact['addressbook_id'], $contact['addressbook_id']);
		}

		$this->cache_contact($contact['id']);
		return $r;
	}

	function get_contact($contact_id) {
		$this->query("SELECT ab_addressbooks.acl_id, ab_contacts.*, ".
				"ab_addressbooks.default_salutation AS default_salutation, ".
				"ab_companies.address AS work_address, ab_companies.address_no AS ".
				"work_address_no, ab_companies.zip AS work_zip, ".
				"ab_companies.city AS work_city, ab_companies.state AS work_state, ".
				"ab_companies.country AS work_country, ab_companies.homepage, ".
				"ab_companies.bank_no, ab_companies.email AS company_email, ".
				"ab_companies.phone AS company_phone, ab_companies.fax AS company_fax, ".
				"ab_companies.name AS company_name, ".
				"ab_companies.name2 AS company_name2, ".
				"ab_companies.post_address AS work_post_address, ab_companies.post_address_no AS work_post_address_no, ".
				"ab_companies.post_zip AS work_post_zip, ab_companies.post_city AS work_post_city, ab_companies.post_state AS work_post_state, ".
				"ab_companies.post_country AS work_post_country ".
				"FROM ab_contacts LEFT JOIN ab_companies ON (ab_contacts.company_id=ab_companies.id) ".
				"INNER JOIN ab_addressbooks ON (ab_contacts.addressbook_id=ab_addressbooks.id) ".
			"WHERE ab_contacts.id='".$this->escape($contact_id)."'");

		return $this->next_record(DB_ASSOC);
		
	}

	function delete_contact($contact_id, $contact=false) {
		
		return GO_Addressbook_Model_Contact::model()->findByPk($contact_id)->delete();

//		global $GO_CONFIG, $GO_MODULES;
//
//		if(!$contact) $contact = $this->get_contact($contact_id);
//
//		if(isset($GLOBALS['GO_MODULES']->modules['files'])) {
//			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
//			$files = new files();
//			try {
//				$files->delete_folder($contact['files_folder_id']);
//			}
//			catch(Exception $e ){}
//		}
//
//		if(isset($GLOBALS['GO_MODULES']->modules['mailings'])) {
//			$sql1 = "DELETE FROM ml_mailing_contacts WHERE contact_id='".$this->escape($contact_id)."'";
//			$this->query($sql1);
//		}
//
//		require_once($GLOBALS['GO_CONFIG']->class_path.'base/search.class.inc.php');
//		$search = new search();
//		$search->delete_search_result($contact_id, 2);
//
//		return $this->query("DELETE FROM ab_contacts WHERE id='".$this->escape($contact_id)."'");

	}

	/**
	 *
	 * Function used to lookup writable contacts
	 *
	 * @param <type> $user_id
	 * @param StringHelper $query
	 * @param <type> $start
	 * @param <type> $offset
	 * @param <type> $sort_index
	 * @param <type> $sort_order
	 * @return <type>
	 */

	function search_contacts_email($user_id, $query, $start=0, $offset=0, $sort_index='name', $sort_order='ASC'){

		if($sort_index=='name') {
			if ($_SESSION['GO_SESSION']['sort_name'] == 'first_name') {
				$sort_index = 'first_name '.$sort_order.', last_name';
			} else {
				$sort_index = 'last_name '.$sort_order.', first_name';
			}
		}

		$fields = "c.id, c.addressbook_id, c.first_name, c.middle_name, c.last_name, co.name AS company_name,c.department,c.function";

		if($query!='')
			$query = '%'.$this->escape(str_replace(' ','%', $query)).'%';

		$conditions = "WHERE ";


//		$user_ab = array();//$this->get_user_addressbook_ids($user_id);
//
//		$this->get_writable_addressbooks($user_id);
//		while($r=$this->next_record()){
//			$user_ab[]=$r['id'];
//		}
		
		$user_ab = $this->get_user_addressbook_ids($user_id);

		if(count($user_ab) > 1) {
			$conditions .= " c.addressbook_id IN (".implode(",",$user_ab).") ";
		}elseif(count($user_ab)==1) {
			$conditions .= " c.addressbook_id=".$user_ab[0]." ";
		}else {
			return false;
		}

		$conditions .= "AND c.email != '' ";

		if(!empty($query)){
			$conditions .= "AND (CONCAT(c.first_name,c.middle_name,c.last_name) LIKE '$query' OR c.email LIKE '$query' OR co.name LIKE '$query')";
		}

		$sql = "SELECT ";

		if($offset>0) {
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}

		$sql .= "$fields, c.email FROM ab_contacts c LEFT JOIN ab_companies co ON co.id=c.company_id $conditions ".
			"UNION SELECT $fields, email2 AS email FROM ab_contacts c LEFT JOIN ab_companies co ON co.id=c.company_id ".str_replace('email', 'email2', $conditions)." ".
			"UNION SELECT $fields, email3 AS email FROM ab_contacts c LEFT JOIN ab_companies co ON co.id=c.company_id ".str_replace('email', 'email3', $conditions)." ".
			"ORDER BY ".$this->escape($sort_index.' '.$sort_order);

		$this->query($sql);

		if($offset > 0) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}

		$this->query($sql);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}


	function search_contacts($user_id, $query, $field = 'last_name', $addressbooks=array(), $start=0, $offset=0, $require_email=false, $sort_index='name', $sort_order='ASC', $writable_only=false, $query_type='LIKE', $mailings_filter=array(), $advanced_query='') {
		global $GO_MODULES;
		//$query = str_replace('*', '%', $query);

		if($sort_index=='name') {
			if ($_SESSION['GO_SESSION']['sort_name'] == 'first_name') {
				$sort_index = 'ab_contacts.first_name '.$sort_order.', ab_contacts.last_name';
			} else {
				$sort_index = 'ab_contacts.last_name '.$sort_order.', ab_contacts.first_name';
			}
		}elseif($sort_index=='age'){
			$sort_index='birthday';
		}

		

		if(count($mailings_filter)) {
			$sql = "SELECT DISTINCT ";
		}else {
			$sql = "SELECT ";
		}

		if($offset>0) {
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}

	
		if($GLOBALS['GO_MODULES']->has_module('customfields')) {
			$sql .= "cf_ab_contacts.*,";
		}
		
		$sql .= "ab_contacts.*, ab_addressbooks.name AS ab_name, ab_companies.name AS company_name";


		$sql .= " FROM ab_contacts LEFT JOIN ab_companies ON ab_contacts.company_id=ab_companies.id ";
		
		$sql .= " LEFT JOIN ab_addressbooks ON ab_contacts.addressbook_id = ab_addressbooks.id ";

		if($GLOBALS['GO_MODULES']->has_module('customfields')) {
			$sql .= "LEFT JOIN cf_ab_contacts ON cf_ab_contacts.model_id=ab_contacts.id ";
		}

		if(count($mailings_filter)) {
			$sql .= "INNER JOIN ab_addresslist_contacts mc ON mc.contact_id=ab_contacts.id ";
		}


		if($addressbooks && count($addressbooks))
		{
			$sql .= "WHERE ab_contacts.addressbook_id IN (".implode(',', $addressbooks).")";		
		} else {

			if($writable_only) {
				$user_ab = $this->get_writable_addressbook_ids($user_id);
			}else {
				$user_ab = $this->get_user_addressbook_ids($user_id);
			}
			if(count($user_ab) > 1) {
				$sql .= "WHERE ab_contacts.addressbook_id IN (".implode(",",$user_ab).") ";
			}elseif(count($user_ab)==1) {
				$sql .= "WHERE ab_contacts.addressbook_id=".$user_ab[0]." ";
			}else {
				return false;
			}
		}

		if(!empty($query)) {
			$sql .= " AND ";
			
			if(!is_array($field)) {
				unset($_SESSION['GO_SESSION']['addressbook_search_contact_fields']);
				if($field == '') {
					if(!isset($_SESSION['GO_SESSION']['addressbook_search_contact_fields'])) {
						$fields=array('name');
						$fields_sql = "SHOW FIELDS FROM ab_contacts";
						$this->query($fields_sql);
						while($this->next_record()) {
							if(stripos($this->f('Type'),'varchar')!==false) {
								$fields[]='ab_contacts.'.$this->f('Field');
							}
						}
						if($GLOBALS['GO_MODULES']->has_module('customfields')) {
							$fields_sql = "SHOW FIELDS FROM cf_ab_contacts";
							$this->query($fields_sql);
							while ($this->next_record()) {
								$fields[]='cf_ab_contacts.'.$this->f('Field');
							}
						}						
						$fields[] = 'ab_companies.name';
						
						$_SESSION['GO_SESSION']['addressbook_search_contact_fields']=$fields;
					}else {
						$fields=$_SESSION['GO_SESSION']['addressbook_search_contact_fields'];
					}
				}else {
					$fields[]=$field;
				}
			}else {
				$fields=$field;
			}

			foreach($fields as $field) {
				if(count($fields)>1) {
					if(isset($first)) {
						$sql .= ' OR ';
					}else {
						$first = true;
						$sql .= '(';
					}
				}

				if($field=='name') {
					$sql .= "CONCAT(first_name,middle_name,last_name) $query_type '".$this->escape(str_replace(' ','%', $query))."' ";
				}else {
					$sql .= "$field $query_type '".$this->escape($query)."' ";
				}
			}
			if(count($fields)>1) {
				$sql .= ')';
			}
		}


		if($require_email) {
			$sql .= " AND ab_contacts.email != ''";
		}

		if(count($mailings_filter)) {
			$sql .= " AND mc.addresslist_id IN (".implode(',', $mailings_filter).")";
		}

		if(!empty($advanced_query)) {
			if(!String::check_parentheses($advanced_query)){
				throw new Exception($GLOBALS['lang']['common']['parentheses_invalid_error']);
			}
			$sql .= ' AND ('.$advanced_query.')';
			
		}

		$sql .= " ORDER BY ".$this->escape($sort_index.' '.$sort_order);


		$_SESSION['GO_SESSION']['export_queries']['search_contacts']=array(
				'query'=>$sql,
				'method'=>'format_contact_record',
				'class'=>'addressbook',
				'require'=>__FILE__);

		if($offset > 0) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}

		$this->query($sql);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}

	public static function format_contact_record(&$record, $cf=false, $html=true) {
		$record['name'] = String::format_name($record['last_name'], $record['first_name'], $record['middle_name']);
		$record['ctime']=Date::get_timestamp($record['ctime']);
		$record['mtime']=Date::get_timestamp($record['mtime']);


		if(!isset($GLOBALS['now']))
			$GLOBALS['now']=time();

		$record['age']='';

		if($record['birthday']!='0000-00-00'){
			$btime = strtotime($record['birthday']);
			$age = date('Y')-date('Y', $btime);

			$month = date('n');
			$bmonth = date('n', $btime);

			if($month<$bmonth || ($month==$bmonth && date('j')<date('j', $btime))) {
				$age--;
			}
			$record['age']=$age;
		}

		$record['birthday'] = Date::format($record['birthday'], false);

		if($cf)
			$cf->format_record($record, 2, $html);
	}


	public static function format_company_record(&$record, $cf=false, $html=true) {
		$record['ctime']=Date::get_timestamp($record['ctime']);
		$record['mtime']=Date::get_timestamp($record['mtime']);

		$record['name_and_name2']=$record['name'];
		if(!empty($record['name2']))
			$record['name_and_name2'].= ' - '.$record['name2'];

		if($cf)
			$cf->format_record($record, 3, $html);
	}

	function search_companies($user_id, $query, $field = 'name', $addressbooks=array(), $start=0, $offset=0, $require_email=false, $sort_index='name', $sort_order='ASC', $query_type='LIKE', $mailings_filter=array(), $advanced_query='') {
		global $GO_MODULES;

		//$query = str_replace('*', '%', $query);

		if(count($mailings_filter)) {
			$sql = "SELECT DISTINCT ";
		}else {
			$sql = "SELECT ";
		}

		if($offset>0) {
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}

		if(isset($GLOBALS['GO_MODULES']->modules['customfields'])) {
			$sql .= "cf_ab_companies.*,ab_companies.* FROM ab_companies ".
					"LEFT JOIN cf_ab_companies ON cf_ab_companies.model_id=ab_companies.id ";
		}else {
			$sql .= "ab_companies.* FROM ab_companies ";
		}

		if(count($mailings_filter)) {
			$sql .= "INNER JOIN ab_addresslist_companies mc ON mc.company_id=ab_companies.id ";
		}

		if($addressbooks && count($addressbooks))
		{
			$sql .= "WHERE ab_companies.addressbook_id IN (".implode(',', $addressbooks).")";
		} else {

			$user_ab = $this->get_user_addressbook_ids($user_id);
			if(count($user_ab) > 1) {
				$sql .= "WHERE ab_companies.addressbook_id IN (".implode(",",$user_ab).")";
			}elseif(count($user_ab)==1) {
				$sql .= "WHERE ab_companies.addressbook_id=".$user_ab[0];
			}else {
				return false;
			}
		}


		if(!empty($query)) {
			$sql .= " AND ";

			if(!is_array($field)) {
				if($field == '') {
					if(!isset($_SESSION['GO_SESSION']['addressbook_search_company_fields'])) {
						$fields=array();
						$fields_sql = "SHOW FIELDS FROM ab_companies";
						$this->query($fields_sql);
						while($this->next_record()) {
							if(stripos($this->f('Type'),'varchar')!==false) {
								$fields[]='ab_companies.'.$this->f('Field');
							}
						}
						if($GLOBALS['GO_MODULES']->has_module('customfields')) {
							$fields_sql = "SHOW FIELDS FROM cf_ab_companies";
							$this->query($fields_sql);
							while ($this->next_record()) {
								$fields[]='cf_ab_companies.'.$this->f('Field');
							}
						}
						$_SESSION['GO_SESSION']['addressbook_search_company_fields']=$fields;
					}else {
						$fields=$_SESSION['GO_SESSION']['addressbook_search_company_fields'];
					}
				}else {
					$fields[]=$field;
				}
			}else {
				$fields=$field;
			}

			foreach($fields as $field) {
				if(count($fields)>1) {
					if(isset($first)) {
						$sql .= ' OR ';
					}else {
						$first = true;
						$sql .= '(';
					}
				}

				$sql .= "$field $query_type '".$this->escape($query)."' ";
			}
			if(count($fields)>1) {
				$sql .= ')';
			}
		}

		if($require_email) {
			$sql .= " AND ab_companies.email != ''";
		}

		if(count($mailings_filter)) {
			$sql .= " AND mc.addresslist_id IN (".implode(',', $mailings_filter).")";
		}

		if(!empty($advanced_query)) {
			if(!String::check_parentheses($advanced_query)){
				throw new Exception($GLOBALS['lang']['common']['parentheses_invalid_error']);
			}
			$sql .= ' AND ('.$advanced_query.')';
		}

		$sql .= " ORDER BY ".$this->escape($sort_index.' '.$sort_order);
		
		$_SESSION['GO_SESSION']['export_queries']['search_companies']=array(
				'query'=>$sql,
				'method'=>'format_company_record',
				'class'=>'addressbook',
				'require'=>__FILE__);

		if($offset > 0 ) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}
		$this->query($sql);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}

	function add_addressbook($user_id, $name, $default_iso_address_format = '', $default_salutation = '') {
		global $GO_SECURITY, $GO_MODULES,$lang;

		if(empty($default_iso_address_format)){
			$default_iso_address_format=$_SESSION['GO_SESSION']['country'];
		}

		if(empty($default_salutation)){
			$default_salutation= $lang['common']['dear'].' ['.$lang['common']['sirMadam']['M'].'/'.$lang['common']['sirMadam']['F'].'] {middle_name} {last_name}';
		}


		$result['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl('addressbook', $user_id);
		$result['user_id']=$user_id;
		$result['default_iso_address_format']=$default_iso_address_format;
		$result['default_salutation']=$default_salutation;
		$result['name']=$name;

		$this->_add_addressbook($result);
		$result['addressbook_id']=$result['id'];
		return $result;
	}

	function _add_addressbook(&$addressbook) {
		global $GO_MODULES;
		
		$addressbook['id'] = $this->nextid('ab_addressbooks');
		if(isset($GLOBALS['GO_MODULES']->modules['files'])) {
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();

			$files->check_share('contacts/'.File::strip_invalid_chars($addressbook['name']),$addressbook['user_id'], $addressbook['acl_id']);
			$files->check_share('companies/'.File::strip_invalid_chars($addressbook['name']),$addressbook['user_id'], $addressbook['acl_id']);
		}

		$this->insert_row('ab_addressbooks', $addressbook);
		return $addressbook['id'];
	}


	function update_addressbook($addressbook, $old_addressbook=false) {

		if(!$old_addressbook)$old_addressbook=$this->get_addressbook($addressbook['id']);

		global $GO_MODULES;
		if(isset($GLOBALS['GO_MODULES']->modules['files']) && $old_addressbook &&  $addressbook['name']!=$old_addressbook['name']) {
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();
			$files->move_by_paths('contacts/'.File::strip_invalid_chars($old_addressbook['name']), 'contacts/'.File::strip_invalid_chars($addressbook['name']));
			$files->move_by_paths('companies/'.File::strip_invalid_chars($old_addressbook['name']), 'companies/'.File::strip_invalid_chars($addressbook['name']));
		}

		global $GO_SECURITY;
		//user id of the addressbook changed. Change the owner of the ACL as well
		if(isset($addressbook['user_id']) && $old_addressbook['user_id'] != $addressbook['user_id']) {
			$GLOBALS['GO_SECURITY']->chown_acl($old_addressbook['acl_id'], $addressbook['user_id']);
		}

		return $this->update_row('ab_addressbooks', 'id', $addressbook);

	}

	function get_addressbook_by_name($name) {
		$sql = "SELECT * FROM ab_addressbooks WHERE name='".$this->escape($name)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->record;
		} else {
			return false;
		}
	}

	function delete_addressbook($addressbook_id) {

		$addressbook = $this->get_addressbook($addressbook_id);

		global $GO_SECURITY, $GO_MODULES, $GO_EVENTS;

		if(isset($GLOBALS['GO_MODULES']->modules['files'])) {
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();

			$folder = $files->resolve_path('contacts/'.File::strip_invalid_chars($addressbook['name']));

			if($folder) {
				$files->delete_folder($folder);
			}

			$folder = $files->resolve_path('companies/'.File::strip_invalid_chars($addressbook['name']));
			if($folder) {
				$files->delete_folder($folder);
			}
		}

		if(empty($addressbook['shared_acl'])) {
			$GLOBALS['GO_SECURITY']->delete_acl($addressbook['acl_id']);
		}

		$ab = new addressbook();

		$this->get_contacts($addressbook_id);
		$contact_ids = array();
		while($contact=$this->next_record()) {
			$contact_id = $this->f('id');
			$ab->delete_contact($contact_id, $contact);
			$contact_ids[] = $contact_id;
		}

		$this->get_companies($addressbook_id);
		while($this->next_record()) {
			$ab->delete_company($this->f('id'));
		}

		$sql = "DELETE FROM ab_addressbooks WHERE id='".$this->escape($addressbook_id)."'";
		$this->query($sql);

		if(count($contact_ids))
			$GLOBALS['GO_EVENTS']->fire_event('addressbook_delete', array($contact_ids));

		if(isset($GLOBALS['GO_MODULES']->modules['sync'])) {
			$sql = "DELETE FROM sync_addressbook_user WHERE addressbook_id='".$this->escape($addressbook_id)."'";
			$this->query($sql);
		}
	}

	function search_email($user_id, $query) {

		$query = $this->escape(str_replace(' ','%', $query));

		$sql = "SELECT DISTINCT a.name AS addressbook_name, c.first_name, c.middle_name, c.last_name, c.email, c.email2, c.email3 FROM ab_contacts c INNER JOIN ab_addressbooks a ON c.addressbook_id=a.id WHERE ";

		$user_ab = $this->get_user_addressbook_ids($user_id);
		if(count($user_ab) > 1) {
			$sql .= "c.addressbook_id IN (".implode(",",$user_ab).") AND ";
		}elseif(count($user_ab)==1) {
			$sql .= "c.addressbook_id=".$user_ab[0]." AND ";
		}else {
			return false;
		}
		$sql .= "(CONCAT(first_name,middle_name,last_name) LIKE '".$query."' OR email LIKE '".$query."' OR email2 LIKE '".$query."' OR email3 LIKE '".$query."')";

		if ($_SESSION['GO_SESSION']['sort_name'] == 'first_name') {
			$sort_index = 'c.first_name ASC, c.last_name';
		} else {
			$sort_index = 'c.last_name ASC, c.first_name';
		}

		$sql .= " ORDER BY $sort_index ASC LIMIT 0,10";

		$this->query($sql);
	}

	function search_company_email($user_id, $query) {

		$query = $this->escape(str_replace(' ','%', $query));

		$sql = "SELECT DISTINCT a.name AS addressbook_name, c.name, c.email FROM ab_companies c INNER JOIN ab_addressbooks a ON a.id=c.addressbook_id WHERE ";

		$user_ab = $this->get_user_addressbook_ids($user_id);
		if(count($user_ab) > 1) {
			$sql .= "c.addressbook_id IN (".implode(",",$user_ab).") AND ";
		}elseif(count($user_ab)==1) {
			$sql .= "c.addressbook_id=".$user_ab[0]." AND ";
		}else {
			return false;
		}
		$sql .= "(c.name LIKE '".$query."' OR c.email LIKE '".$query."') AND c.email!=''";
		$sql .= " ORDER BY name ASC LIMIT 0,10";

		$this->query($sql);
	}

	/**
	 * When a an item gets deleted in a panel with links. Group-Office attempts
	 * to delete the item by finding the associated module class and this function
	 *
	 * @param int $id The id of the linked item
	 * @param int $link_type The link type of the item. See /classes/base/links.class.inc
	 */

	function __on_delete_link($id, $link_type) {
	//echo $id.':'.$link_type;
		if($link_type==3) {
			$this->delete_company($id);
		}elseif($link_type==2) {
			$this->delete_contact($id);
		}
	}


	/**
	 * Adds or updates a note in the search cache table
	 *
	 * @param int $note_id
	 */
	private function cache_contact($contact_id) {
		global $GO_CONFIG, $GO_LANGUAGE;
		require_once($GLOBALS['GO_CONFIG']->class_path.'/base/search.class.inc.php');
		$search = new search();

		require($GLOBALS['GO_LANGUAGE']->get_language_file('addressbook'));

		$sql = "SELECT c.*,a.acl_id, a.name AS addressbook_name, co.name AS company FROM ab_contacts c ".
			"INNER JOIN ab_addressbooks a ON a.id=c.addressbook_id ".
			"LEFT JOIN ab_companies co ON co.id=c.company_id ".
			"WHERE c.id=?";
		$this->query($sql, 'i', $contact_id);
		$record = $this->next_record();
		if($record) {
			$cache['id']=$this->f('id');
			$cache['user_id']=$this->f('user_id');
			$cache['module']='addressbook';
			$cache['name'] = htmlspecialchars(String::format_name($this->f('last_name'),$this->f('first_name'),$this->f('middle_name')), ENT_QUOTES,'UTF-8');

			if($record['company']!='')
				$cache['name'] .= ' ('.htmlspecialchars($record['company'], ENT_QUOTES,'UTF-8').')';

			$cache['link_type']=2;
			$cache['description']=$this->f('addressbook_name');//.', '.$this->f('company');
			$cache['type']=$lang['addressbook']['contact'];
			$cache['keywords']=$search->record_to_keywords($this->record).','.$lang['addressbook']['contact'];
			$cache['mtime']=$this->f('mtime');
			$cache['acl_id']=$this->f('acl_id');

			$search->cache_search_result($cache);
		}
	}

	/**
	 * Adds or updates a note in the search cache table
	 *
	 * @param int $note_id
	 */
	private function cache_company($company_id) {
		global $GO_CONFIG, $GO_LANGUAGE;
		require_once($GLOBALS['GO_CONFIG']->class_path.'/base/search.class.inc.php');
		$search = new search();
		require($GLOBALS['GO_LANGUAGE']->get_language_file('addressbook'));
		$sql = "SELECT c.*, a.acl_id,  a.name AS addressbook_name FROM ab_companies c INNER JOIN ab_addressbooks a ON a.id=c.addressbook_id WHERE c.id=?";
		$this->query($sql, 'i', $company_id);
		$record = $this->next_record();
		if($record) {
			$cache['id']=$this->f('id');
			$cache['user_id']=$this->f('user_id');
			$cache['name'] = htmlspecialchars($this->f('name').' ('.$this->f('addressbook_name').')', ENT_QUOTES, 'utf-8');
			$cache['link_type']=3;
			$cache['module']='addressbook';
			$cache['description']='';
			$cache['type']=$lang['addressbook']['company'];
			$cache['keywords']=$search->record_to_keywords($this->record).','.$cache['type'];
			$cache['mtime']=$this->f('mtime');
			$cache['acl_id']=$this->f('acl_id');

			$search->cache_search_result($cache);
		}
	}

	/**
	 * When a global search action is performed this function will be called for each module
	 *
	 * @param int $last_sync_time The time this function was called last
	 */

	public static function build_search_index() {
		$ab = new addressbook();
		$ab2 = new addressbook();

		$sql = "SELECT id FROM ab_contacts";
		$ab2->query($sql);

		while($record = $ab2->next_record()) {
			$ab->cache_contact($record['id']);
		}

		$sql = "SELECT id FROM ab_companies";
		$ab2->query($sql);
		while($record = $ab2->next_record()) {
			$ab->cache_company($record['id']);
		}
	}

	/**
	 * This function is called when a user is deleted
	 *
	 * @param int $user_id
	 */

	public static function user_delete($user) {

		$ab2 = new addressbook();

		$ab = new addressbook();

		$sql = "SELECT id FROM ab_addressbooks WHERE user_id='".$ab2->escape($user['id'])."'";
		$ab2->query($sql);
		while ($ab2->next_record()) {
			$ab->delete_addressbook($ab2->f('id'));
		}
	}

	function move_contacts_company($company_id, $old_addressbook_id, $addressbook_id, $update_company=true) {
		if($company_id>0) {
			$this->query("SELECT * FROM ab_contacts WHERE company_id=? AND addressbook_id=?", 'ii', array($company_id, $old_addressbook_id));
			while($contact = $this->next_record()) {
				$contact['addressbook_id'] = $addressbook_id;
				$this->update_contact($contact);
			}

			if($update_company) {
				$this->query('UPDATE ab_companies SET addressbook_id=? WHERE id=?', 'ii', array($addressbook_id, $company_id));
			}
		}
	}

	function get_contact_by_email($email, $user_id, $addressbook_id=0) {
		$this->get_contacts_by_email($email, $user_id, $addressbook_id,0,1);
		return $this->next_record();
	}

	function get_index_char($string) {
		$char = '';
		if (!empty($string)) {
			if (function_exists('mb_substr')) {
				$char = strtoupper(mb_substr(File::strip_invalid_chars($string),0,1,'UTF-8'));
			} else {
				$char = strtoupper(substr(File::strip_invalid_chars($string),0,1));
			}
		}

		return $char;
	}

	function get_contacts_by_email($email, $user_id, $addressbook_id=0, $start=0, $offset=0, $count=false) {
		$email = $this->escape(String::get_email_from_string($email));
		$sql = "SELECT";

		if($count && $offset>0) {
			$sql .= " SQL_CALC_FOUND_ROWS";
		}

		$sql .= " * FROM ab_contacts ";

		if($addressbook_id>0) {
			$sql .= "WHERE addressbook_id=".intval($addressbook_id)." AND ";
		}else {
			$user_ab = $this->get_user_addressbook_ids($user_id);
			if(count($user_ab) > 1) {
				$sql .= "WHERE addressbook_id IN (".implode(",",$user_ab).") AND ";
			}elseif(count($user_ab)==1) {
				$sql .= "WHERE addressbook_id=".$user_ab[0]." AND ";
			}else {
				return false;
			}
		}
		$sql .= " (email='$email' OR email2='$email' OR email3='$email')";

		if($offset > 0) {
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}

		$this->query($sql);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}

	function save_sql($sql) {
		$sql['id'] = $this->nextid('ab_sql');
		return $this->insert_row('ab_sql',$sql);
	}

	function get_sqls($user_id, $companies='0') {
		$this->query("SELECT * FROM ab_sql WHERE user_id='".intval($user_id)."' AND companies='".$this->escape($companies)."'");
		return $this->num_rows();
	}

	function get_sql($id) {
		$this->query("SELECT * FROM ab_sql WHERE id='".$this->$escape($id)."'");
		return $this->next_record();
	}

	function delete_sql($sql_id) {

		return $this->query("DELETE FROM ab_sql WHERE id='".$this->escape($sql_id)."'");

	}

	/**
	 * Checks whether the addressbook's customfield categories should be managed
	 * seperately (return true) or all the categories should be shown (return false).
	 * Possible values for the second paramater are 2 (for contacts) and 3 (for companies).
	 * @param Int $addressbook_id
	 * @param Int $cf_type
	 * @return Boolean
	 */
	function check_addressbook_category_limit($addressbook_id,$cf_type=2) {
		$sql = "SELECT * FROM cf_addressbook_limits WHERE addressbook_id='".$this->escape($addressbook_id)."'; ";
		$this->query($sql);
		if ($this->num_rows()>0) {
			$record = $this->next_record();
			switch ($cf_type) {
				case '3':
					$typestring = 'companies';
					break;
				default:
					$typestring = 'contacts';
					break;
			}
			return !empty($record['limit_'.$typestring.'_cf_categories']);
		} else {
			return false;
		}
	}

	/**
	 * Gets the customfield categories associated with an addressbook that can be
	 * shown. Possible values for the second paramater are 2 (for contacts) and 3
	 * (for companies).
	 * @param Int $addressbook_id
	 * @param Int $cf_type
	 * @return Int num_rows
	 */
	function get_allowed_categories($addressbook_id,$cf_type=2) {
		switch ($cf_type) {
			case '3':
				$typestring = 'companies';
				break;
			default:
				$typestring = 'contacts';
				break;
		}
		$sql = "SELECT * FROM cf_".$typestring."_cf_categories WHERE addressbook_id='".$this->escape($addressbook_id)."'; ";

		$this->query($sql);
		return $this->num_rows();
	}

	/**
	 * Clears all associations of customfield categories with contacts (cf_type 2)
	 * or companies (cf_type 3), given the addressbook id.
	 * @param Int $addressbook_id
	 * @param Int $cf_type
	 * @return Boolean true on success
	 */
	function clear_addressbook_cf_categories($addressbook_id,$cf_type=2) {
		switch ($cf_type) {
			case '3':
				$typestring = 'companies';
				break;
			default:
				$typestring = 'contacts';
				break;
		}
		$sql = "DELETE FROM `cf_".$typestring."_cf_categories` WHERE addressbook_id='".$this->escape($addressbook_id)."'; ";
		return $this->query($sql);
	}

	/**
	 * Associates a customfield category with an addressbook. Possible values for
	 * the third paramater are 2 (for contacts) and 3 (for companies).
	 * @param Int $addressbook_id
	 * @param Int $category_id
	 * @param Int $cf_type
	 * @return Boolean true on success
	 */
	function add_addressbook_cf_category($addressbook_id,$category_id,$cf_type=2) {
		switch ($cf_type) {
			case '3':
				$typestring = 'companies';
				break;
			default:
				$typestring = 'contacts';
				break;
		}
		$record['addressbook_id'] = $this->escape($addressbook_id);
		$record['category_id'] = $this->escape($category_id);
		return $this->insert_row("cf_".$typestring."_cf_categories",$record);
	}

	function toggle_addressbook_limit($addressbook_id,$enable=false,$cf_type=2) {
		switch ($cf_type) {
			case '3':
				$typestring = 'companies';
				break;
			default:
				$typestring = 'contacts';
				break;
		}
		$check_exists = "SELECT * FROM `cf_addressbook_limits` WHERE addressbook_id='".$this->escape($addressbook_id)."'; ";
		$this->query($check_exists);
		if ($this->num_rows()>0) {
			$up_record['addressbook_id'] = $this->escape($addressbook_id);
			$up_record['limit_'.$typestring.'_cf_categories'] = $enable ? 1 : 0;
			return $this->update_row('cf_addressbook_limits','addressbook_id',$up_record);
		} elseif ($enable) {
			$up_record['addressbook_id'] = $this->escape($addressbook_id);
			$up_record['limit_'.$typestring.'_cf_categories'] = 1;
			return $this->insert_row('cf_addressbook_limits',$up_record);
		}
		return true;
	}

	function get_addressbooks_limits_array($user_id) {
		global $GO_MODULES;
		require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'].'addressbook.class.inc.php');
		$ab = new addressbook();
		$addressbook_ids = $ab->get_user_addressbook_ids($user_id);
		$out_array = array();
		foreach ($addressbook_ids as $ab_id) {
			$record['limit_contacts'] = $this->check_addressbook_category_limit($ab_id, 2);
			$record['limit_companies'] = $this->check_addressbook_category_limit($ab_id, 3);
			$record['companies_categories'] = array();
			$record['contacts_categories'] = array();
			$this->get_allowed_categories($ab_id,2);
			while ($record2 = $this->next_record())
				$record['contacts_categories'][] = $record2['category_id'];
			$this->get_allowed_categories($ab_id,3);
			while ($record2 = $this->next_record())
				$record['companies_categories'][] = $record2['category_id'];
			$out_array[$ab_id] = $record;
		}
		return $out_array;
	}

	function get_addressbook_cf_category_permissions(&$response) {
		global $GO_MODULES,$GO_SECURITY;
		require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
		require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'].'addressbook.class.inc.php');
		$cf = new customfields();
		$ab = new addressbook();
		$response['data']['cf_permissions'] = array();
		$cf->get_authorized_categories(2,$GLOBALS['GO_SECURITY']->user_id);
		while ($cat = $cf->next_record()) {
			$response['data']['cf_permissions']['contact_categories_allowed_from_cf_module'] = true;
		}
		$cf->get_authorized_categories(3,$GLOBALS['GO_SECURITY']->user_id);
		while ($cat = $cf->next_record()) {
			$response['data']['cf_permissions']['company_categories_allowed_from_cf_module'] = true;
		}
		$folder_cf_data = $ab->get_addressbooks_limits_array($GLOBALS['GO_SECURITY']->user_id);
		$response['data']['cf_permissions']['allowed_for_addressbook'] = $folder_cf_data[$response['data']['addressbook_id']];
		return $response['data']['cf_permissions'];
	}

	/**
	 * For use with the script that forwards contact / company details to the
	 * front-end. This function adds the customfield category ids that the current
	 * user may see in the front-end to the record.
	 * @global Object $GO_MODULES
	 * @global Object $GO_SECURITY
	 * @param Array $record The contact or company record.
	 * @param String $identifier
	 * @return Array $record The record updated with the
	 */
	function cf_categories_to_record($record, $identifier='id') {
		global $GO_MODULES;
		if($GLOBALS['GO_MODULES']->has_module('customfields'))
		{
			require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
			require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'].'addressbook.class.inc.php');
			$customfields = new customfields();
			$ab = new addressbook();

			global $GO_SECURITY;

			$record['allowed_cf_categories'] = array();

			$authorized_contact_categories = array();
			$customfields->get_authorized_categories(2, $GLOBALS['GO_SECURITY']->user_id);
			while ($record2 = $customfields->next_record()) {
				$authorized_contact_categories[] = $record2['id'];
				unset($record2);
			}

			$contacts_limit = $ab->check_addressbook_category_limit($record[$identifier],2);

			if (!empty($contacts_limit)) {
				$ab->get_allowed_categories($record[$identifier],2);
				while ($record2 = $ab->next_record()) {
//							if (in_array($record2['category_id'],$authorized_contact_categories)) {
						$record['allowed_cf_categories'][] = $record2['category_id'];
//							}
					unset($record2);
				}
			} else {
				$record['allowed_cf_categories'] = array_merge($record['allowed_cf_categories'],$authorized_contact_categories);
			}

			$authorized_company_categories = array();
			$customfields->get_authorized_categories(3, $GLOBALS['GO_SECURITY']->user_id);
			while ($record2 = $customfields->next_record()) {
				$authorized_company_categories[] = $record2['id'];
				unset($record2);
			}

			$companies_limit = $ab->check_addressbook_category_limit($record[$identifier],3);

			if (!empty($companies_limit)) {
				$ab->get_allowed_categories($record[$identifier],3);
				while ($record2 = $ab->next_record()) {
//							if (in_array($record2['category_id'],$authorized_company_categories)) {
						$record['allowed_cf_categories'][] = $record2['category_id'];
//							}
					unset($record2);
				}
			} else {
				$record['allowed_cf_categories'] = array_merge($record['allowed_cf_categories'],$authorized_company_categories);
			}

			$record['allowed_cf_categories'] = implode(',',$record['allowed_cf_categories']);

			return $record;
		}
	}
}