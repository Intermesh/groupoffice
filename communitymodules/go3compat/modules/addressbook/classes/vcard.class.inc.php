<?php
/*
   Copyright Lorenz Softwareentwicklung & Systemintegration 2004
   Author: Georg Lorenz <georg@lonux.de>
   Version: 0.99 Release date: 17 May 2004

   This program is free software; you can redistribute it and/or modify it
   under the terms of the GNU General Public License as published by the
   Free Software Foundation; either version 2 of the License, or (at your
   option) any later version.
 */

define("ADR_POBOX", "ADR_0");
define("ADR_EXTENDED", "ADR_1");
define("ADR_STREET", "ADR_2");
define("ADR_LOCALITY", "ADR_3");
define("ADR_REGION", "ADR_4");
define("ADR_POSTALCODE", "ADR_5");
define("ADR_COUNTRY", "ADR_6");

define("N_FAMILY", "N_0");
define("N_GIVEN", "N_1");
define("N_ADDITIONAL", "N_2");
define("N_PREFIX", "N_3");
define("N_SUFFIX", "N_4");

define("ORG_NAME", "ORG_0");
define("ORG_UNIT", "ORG_1");
define("ORG_OPTIONAL_UNIT", "ORG_2");

define("GO_SALUTATION", "X-GO-SALUTATION");
define("GO_COMPANY_POST_ADDRESS", "X-GO-COMPANY-POST-ADDRESS");
define("GO_COMPANY_POST_ADDRESS_NO", "X-GO-COMPANY-POST-ADDRESS-NO");
define("GO_COMPANY_POST_CITY", "X-GO-COMPANY-POST-CITY");
define("GO_COMPANY_POST_ZIP", "X-GO-COMPANY-POST-ZIP");
define("GO_COMPANY_POST_STATE", "X-GO-COMPANY-POST-STATE");
define("GO_COMPANY_POST_COUNTRY", "X-GO-COMPANY-POST-COUNTRY");
define("GO_COMPANY_TEL", "X-GO-COMPANY-TEL");
define("GO_COMPANY_FAX", "X-GO-COMPANY-FAX");
define("GO_COMPANY_URL", "X-GO-COMPANY-URL");
define("GO_COMPANY_EMAIL", "X-GO-COMPANY-EMAIL");
define("GO_COMPANY_BANK_NO", "X-GO-COMPANY-BANK-NO");
define("GO_COMPANY_VAT_NO", "X-GO-COMPANY-VAT-NO");

define("PARM_TYPE", "TYPE");
define("PARM_ENCODING", "ENCODING");
define("PARM_VALUE", "VALUE");
define("PARM_CHARSET", "CHARSET");
define("PARM_LANGUAGE", "LANGUAGE");
define("DELIM_DOT", ".");
define("DELIM_COLON", ":");
define("DELIM_SEMICOLON", ";");
define("DELIM_COMMA", ",");
define("DELIM_EQUAL", "=");
define("LINE_LENGTH", 75);
define("FOLDING_CHAR", chr(13).chr(10).chr(32));
define("WORD_WRAP_DOS", chr(13).chr(10));
define("WORD_WRAP_MAC", chr(13));
define("WORD_WRAP_UNIX", chr(10));
define("CHAR_WSP", chr(32));
define("CHAR_HTAB", chr(9));


/**
* vCard class containing methods and properties
* for dealing with vCard files (vcf)
*
* @package  addressbook vcf class
* @author   Georg Lorenz <georg@lonux.de>
* @since    Group-Office 2.06
*/

class vcard extends addressbook {
	var $index;
	var $instance;
	var $version;
	var $revision;
	var $vcf;

	var $add_leading_space_to_qp_encoded_line_wraps=false;

	function vcard() {
		$this->index = null;
		$this->instance = array ();
		$this->version = 'VERSION:2.1';
		$this->_set_revision();
		$this->vcf = '';

		parent::__construct();
	}

	/**
	* Imports personal data from a vcf file into users addressbook.
	*
	* @param  StringHelper $file			Contains the file name to be imported.
	* @param  int $user_id			Users id.
	* @param  int $addressbook_id	Addressbook id.
	* @access public
	* @return boolean
	*/
	function import($file, $user_id, $addressbook_id) {
		if ($content = $this->_get_file_content($file)) {
			if ($this->_set_vcard($content, "file")) {
				foreach ($this->instance as $vcard) {
					$record = $this->_get_vcard_contact($vcard);
					$contact = $record['contact'];


					global $GO_SECURITY;
					if ($ab = $this->get_addressbook($addressbook_id)) {
						$contact['addressbook_id'] =$addressbook_id;
						if(isset($record['company']) && !empty($record['company']['name']))
						{
							$company = $record['company'];
							$company['addressbook_id']=$contact['addressbook_id'];
							if(!$contact['company_id'] = $this->get_company_id_by_name($company['name'], $addressbook_id))
							{
								$contact['company_id'] = $this->add_company($company);
							}
						}
					//	unset($contact['company_name']);
						$this->add_contact($contact);

					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	function vcf_to_go($vcf_string)
	{
		$vcf_string = str_replace(WORD_WRAP_DOS, WORD_WRAP_UNIX, $vcf_string);
		/*word wrap - replace <CR> by <LF> (mac)*/
		$vcf_string = str_replace(WORD_WRAP_MAC, WORD_WRAP_UNIX, $vcf_string);
		/*unfolding lines ending up in '=<LF>', originally '=<CRLF>'*/
		$regex = '/('.DELIM_EQUAL.WORD_WRAP_UNIX.')/i';
		$vcf_string = preg_replace($regex, "", $vcf_string);
		$regex = '/('.WORD_WRAP_UNIX.')(['.CHAR_WSP.'|'.CHAR_HTAB.'])/i';
		$vcf_string = preg_replace($regex, "", $vcf_string);

		$content = preg_split('/'.WORD_WRAP_UNIX.'/', $vcf_string);

		if($this->_set_vcard($content, "file"))
		{
			return $this->_get_vcard_contact($this->instance[0]);
		}
		return false;
	}

	/**
	* Creates a vcf file from addressbook contacts.
	*
	* @param  int $addressbook_id	Addressbook id.
	* @access public
	* @return StringHelper
	*/
	function export_addressbook($addressbook_id) {
		$records = $this->_get_addressbook($addressbook_id);
		if ($records) {
			return $this->_create_vcard($records);
		}
		return false;
	}

	/**
	* Creates a vcf file for a contact.
	*
	* @param  int $contact_id		Contact id.
	* @access public
	* @return StringHelper
	*/
	function export_contact($contact_id) {
		$records = $this->_get_contact($contact_id);
		if ($records) {
			return $this->_create_vcard($records);
		}
		return false;
	}

	/**
	* Creates a vcf file from users personal data.
	*
	* @param  int $user_id			Users id.
	* @access public
	* @return StringHelper
	*/
	function create($user_id) {
		$records = $this->_get_user($user_id);
		if ($records) {
			return $this->_create_vcard($records);
		}
		return false;
	}

	/**
	* Determines the count of vcards.
	*
	* @param  StringHelper $file			vCard file.
	* @access public
	* @return int
	*/
	function get_count($file) {
		if ($content = $this->_get_file_content($file)) {
			if ($this->_set_vcard($content, "file")) {
				return count($this->instance);
			}
		}
		return false;
	}

	/**
	* Returns the vCard record.
	*
	* @param  int $index			index of vCard.
	* @access public
	* @return array
	*/
	function get_vcard_contact($index) {
		if (isset ($this->instance[$index])) {
			return $this->_get_vcard_contact($this->instance[$index]);
		}
		return false;
	}

	/**
	* Returns the formatted record for database storage.
	*
	* @param  array $vcard			vCard record.
	* @access private
	* @return array
	*/
	function _get_vcard_contact($vcard) {
		$record = array ();
		$record['contact'] = array ('source_id' => '0', 'first_name' => '', 'middle_name' => '', 'last_name' => '', 'title' => '', 'function' => '', 'birthday' => '', 'sex' => 'M', 'initials' => '', 'country' => '', 'state' => '', 'city' => '', 'zip' => '', 'address' => '', 'address_no' => '', 'fax' => '', 'home_phone' => '', 'work_fax' => '', 'work_phone' => '', 'cellular' => '', 'email' => '', 'company_id' => '0', 'company_name' => '', 'department' => '', 'comment' => '');
		//$record['company'] = array ('name' => '', 'homepage' => '', 'country' => '', 'state' => '', 'city' => '', 'zip' => '', 'address' => '', 'address_no' => '', 'phone' => '', 'fax' => '', 'email' => '', 'bank_no' => '', 'vat_no' => '', 'post_address' => '', 'post_address_no' => '', 'post_state' => '', 'post_city' => '', 'post_zip' => '', 'post_country' => '');

		$record['contact'] = array ();//'first_name' => '', 'middle_name' => '', 'last_name' => '', 'fax' => '', 'home_phone' => '', 'work_fax' => '', 'work_phone' => '', 'cellular' => '');
		foreach ($vcard as $property) {

			switch ($property->name) {
				case "N" :

					$record['contact']['title'] = isset ($property->values[N_PREFIX]) ? $property->values[N_PREFIX] : "";
					$record['contact']['last_name'] = isset ($property->values[N_FAMILY]) ? $property->values[N_FAMILY] : "";
					$record['contact']['first_name'] = isset ($property->values[N_GIVEN]) ? $property->values[N_GIVEN] : "";
					$record['contact']['middle_name'] = isset ($property->values[N_ADDITIONAL]) ? $property->values[N_ADDITIONAL] : "";
					//MS: TODO add suffix field to GO?
					break;
				case "TITLE" :
					$record['contact']['function'] = empty ($property->values[0]) ? '' : $property->values[0];
					break;
				case "ROLE" :
					//$record['contact']['function'] = empty ($property->values[0]) ? '' : $property->values[0];
					break;
				case "BDAY" :
					if (!empty ($property->values[0])) {
						if (!is_numeric($property->values[0])) {
							$date = explode("-", $property->values[0]);
							$pos = strpos($date[2], 'T');
							if($pos){
								$date[2]=substr($date[2], 0, $pos);
							}

							$property->values[0] = date("Ymd", mktime(0, 0, 0, $date[1], $date[2], $date[0]));
						}
						$record['contact']['birthday'] = $property->values[0];
					}
					break;
				case "ADR" :
					if (in_array('WORK', $property->parm_types)) {
						$record['company']['country'] = isset ($property->values[ADR_COUNTRY]) ? $property->values[ADR_COUNTRY] : "";
						$record['company']['state'] = isset ($property->values[ADR_REGION]) ? $property->values[ADR_REGION] : "";
						$record['company']['city'] = isset ($property->values[ADR_LOCALITY]) ? $property->values[ADR_LOCALITY] : "";
						$record['company']['zip'] = isset ($property->values[ADR_POSTALCODE]) ? $property->values[ADR_POSTALCODE] : "";

						if(!isset($property->values[ADR_STREET]))
								$property->values[ADR_STREET]='';

						$property->values[ADR_STREET]=str_replace("\r", '', $property->values[ADR_STREET]);

						$lines = explode("\n", $property->values[ADR_STREET]);
						if(count($lines)>1){
							$record['company']['address']=$lines[0];
							$record['company']['address_no']=$lines[1];
						}else
						{
							$record['company']['address']=$this->_get_address($lines[0]);
							$record['company']['address_no']=$this->_get_address_no($lines[0]);
						}
					} elseif(in_array('HOME', $property->parm_types)) {
						$record['contact']['country'] = isset ($property->values[ADR_COUNTRY]) ? $property->values[ADR_COUNTRY] : "";
						$record['contact']['state'] = isset ($property->values[ADR_REGION]) ? $property->values[ADR_REGION] : "";
						$record['contact']['city'] = isset ($property->values[ADR_LOCALITY]) ? $property->values[ADR_LOCALITY] : "";
						$record['contact']['zip'] = isset ($property->values[ADR_POSTALCODE]) ? $property->values[ADR_POSTALCODE] : "";
					
						if(!isset($property->values[ADR_STREET]))
								$property->values[ADR_STREET]='';

						$property->values[ADR_STREET]=str_replace("\r", '', $property->values[ADR_STREET]);

						$lines = explode("\n", $property->values[ADR_STREET]);
						if(count($lines)>1){
							$record['contact']['address']=$lines[0];
							$record['contact']['address_no']=$lines[1];
						}else
						{
							$record['contact']['address']=$this->_get_address($lines[0]);
							$record['contact']['address_no']=$this->_get_address_no($lines[0]);
						}
						
					}
					break;
				case "TEL" :

					//var_dump($property);
					if(!empty($property->values[0])){
						if (in_array('HOME', $property->parm_types)) {

							if (in_array('FAX', $property->parm_types)) {
								$record['contact']['fax'] = $property->values[0];
							}
							if (in_array('VOICE', $property->parm_types)) {
								$record['contact']['home_phone'] = $property->values[0];
							}
							if (!in_array('FAX', $property->parm_types) && !in_array('VOICE', $property->parm_types)) {
								$record['contact']['home_phone'] = $property->values[0];
							}
						}

						if (in_array('WORK', $property->parm_types)) {
							if (in_array('FAX', $property->parm_types)) {
								$record['contact']['work_fax'] = $property->values[0];
							}
							if (in_array('VOICE', $property->parm_types)) {
								$record['contact']['work_phone'] = $property->values[0];
							}
							if (!in_array('FAX', $property->parm_types) && !in_array('VOICE', $property->parm_types)) {
								$record['contact']['work_phone'] = $property->values[0];
							}
						}elseif (in_array('CELL', $property->parm_types)) {
							$record['contact']['cellular'] = $property->values[0];
						}else
						{
							if (in_array('FAX', $property->parm_types)) {
								if(empty($record['contact']['fax']))
								{
									$record['contact']['fax'] = $property->values[0];
								}
							}
							if (in_array('VOICE', $property->parm_types)) {
								if(empty($record['contact']['home_phone']))
								{
									$record['contact']['home_phone'] = $property->values[0];
								}
							}
							if (!in_array('FAX', $property->parm_types) && !in_array('VOICE', $property->parm_types)) {
								if(empty($record['contact']['home_phone']))
								{
									$record['contact']['home_phone'] = $property->values[0];
								}
							}
						}
					}
					break;
				case "EMAIL" :

					/*if(in_array('HOME', $property->parm_types)){
						$record['contact']['email'] = $property->values[0];
					}elseif(in_array('WORK', $property->parm_types)){
						$record['contact']['email2'] = $property->values[0];
					}else*/

					if(!empty($record['contact']['email3']))
					{
						//we ran out of email addres storage sorry.
					}elseif(!empty($record['contact']['email2']))
					{
						$record['contact']['email3'] = $property->values[0];
					}elseif(!empty($record['contact']['email']))
					{
						$record['contact']['email2'] = $property->values[0];
					}else
					{
						$record['contact']['email'] = $property->values[0];
					}
					break;
				case "ORG" :
					//$record['contact']['company_name'] = isset ($property->values[ORG_NAME]) ? $property->values[ORG_NAME] : "";
					$record['contact']['department'] = isset ($property->values[ORG_UNIT]) ? $property->values[ORG_UNIT] : "";
					$record['company']['name'] = isset ($property->values[ORG_NAME]) ? $property->values[ORG_NAME] : "";
					break;
				case "URL" :
					if (in_array('WORK', $property->parm_types)) {
						$record['company']['homepage'] = $property->values[0];
					}
					break;
				case "NOTE" :
					$record['contact']['comment'] = $property->values[0];
					break;
				case "TZ" :
					break;
				case GO_COMPANY_POST_ADDRESS :
					$record['company']['post_address'] = $property->values[0];
					break;
				case GO_COMPANY_POST_ADDRESS_NO :
					$record['company']['post_address_no'] = $property->values[0];
					break;
				case GO_COMPANY_POST_CITY :
					$record['company']['post_city'] = $property->values[0];
					break;
				case GO_COMPANY_POST_ZIP :
					$record['company']['post_zip'] = $property->values[0];
					break;
				case GO_COMPANY_POST_STATE :
					$record['company']['post_state'] = $property->values[0];
					break;
				case GO_COMPANY_POST_COUNTRY :
					$record['company']['post_country'] = $property->values[0];
					break;
				case GO_COMPANY_TEL :
					$record['company']['phone'] = $property->values[0];
					break;
				case GO_COMPANY_FAX :
					$record['company']['fax'] = $property->values[0];
					break;
				case GO_COMPANY_EMAIL :
					$record['company']['email'] = $property->values[0];
					break;
				case GO_COMPANY_BANK_NO :
					$record['company']['bank_no'] = $property->values[0];
					break;
				case GO_COMPANY_VAT_NO :
					$record['company']['vat_no'] = $property->values[0];
					break;
				case GO_SALUTATION :
					$record['contact']['salutation'] = $property->values[0];
					break;
			}
		}
		return $record;
	}

	/**
	* Gets a file content and creates an array line by line.
	*
	* @param  StringHelper $file		Contains the filename (full path).
	* @access private
	* @return array
	*/
	function _get_file_content($file) {
		$content = "";

		if (!$handle = fopen($file, "r")) {
			return false;
		}
		while (!feof($handle)) {
			$line = fgets($handle, 4096);
			if (strlen($line) > 0) {
				/*word wrap - replace <CRLF> by <LF> (dos)*/
				$line = str_replace(WORD_WRAP_DOS, WORD_WRAP_UNIX, $line);
				/*word wrap - replace <CR> by <LF> (mac)*/
				$line = str_replace(WORD_WRAP_MAC, WORD_WRAP_UNIX, $line);
				/*unfolding lines ending up in '=<LF>', originally '=<CRLF>'*/
				$regex = '/('.DELIM_EQUAL.WORD_WRAP_UNIX.')/i';
				$content .= preg_replace($regex, "", $line);
			}
		}
		fclose($handle);

		/*unfolding lines as specified in RFC2425*/
		$regex = '/('.WORD_WRAP_UNIX.')(['.CHAR_WSP.'|'.CHAR_HTAB.'])/i';
		$content = preg_replace($regex, "", $content);

		$content = preg_split('/'.WORD_WRAP_UNIX.'/', $content);
		return $content;
	}



	/**
	* Gets the addressbook content and creates an array containing the records.
	*
	* @param  int $addressbook_id	Contains the addressbook id.
	* @access private
	* @return array
	*/
	function _get_addressbook($addressbook_id) {
		$records = array ();

		if ($this->get_contacts($addressbook_id)) {
			while ($this->next_record()) {
				$records[] = $this->record;
			}
		} else {
			return false;
		}

		for ($i = 0; $i < count($records); $i ++) {
			$records[$i]['address'] .= !empty ($records[$i]['address_no']) ? CHAR_WSP.$records[$i]['address_no'] : "";

			if ($records[$i]['company_id'] > 0) {
				if($company = $this->get_company($records[$i]['company_id']))
				{
					$company['address'] .= !empty ($company['address_no']) ? CHAR_WSP.$company['address_no'] : "";
					foreach ($company as $key => $value) {
						$field_name = 'company_'.$key;
						$records[$i][$field_name] = $value;
					}
				}
			}
		}
		return $records;
	}

	/**
	* Gets the contact record and creates an array containing the record.
	*
	* @param  int $contact_id		Contains the contact id.
	* @access private
	* @return array
	*/
	function _get_contact($contact_id) {
		$records = array ();

		$records[] = $this->get_contact($contact_id);

		if (count($records)) {
			for ($i = 0; $i < count($records); $i ++) {
				$records[$i]['address'] .= !empty ($records[$i]['address_no']) ? CHAR_WSP.$records[$i]['address_no'] : "";

				if ($records[$i]['company_id'] > 0) {
					if($company = $this->get_company($records[$i]['company_id']))
					{
						$company['address'] .= !empty ($company['address_no']) ? CHAR_WSP.$company['address_no'] : "";
						foreach ($company as $key => $value) {
							$field_name = 'company_'.$key;
							$records[$i][$field_name] = $value;
						}
					}
				}
			}
		} else {
			return false;
		}
		return $records;
	}

	/**
	* Gets the users personal data record and creates an array containing the record.
	*
	* @param  int $user_id			Contains the users id.
	* @access private
	* @return array
	*/
	function _get_user($user_id = 0) {
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		$records = array ();

		if (!$user_id) {
			global $GO_SECURITY;
			$user_id = $GLOBALS['GO_SECURITY']->user_id;
		}
		$records[] = $GO_USERS->get_user($user_id);

		if (count($records)) {
			return $records;
		} else {
			return false;
		}
	}

	/*function format_line($name_part, $value_part)
	{
		$value_part = str_replace("\r\n","\n", $value_part);

		$qp_value_part = String::quoted_printable_encode($value_part);

		if($value_part != $qp_value_part || strlen($name_part.$value_part)>=73)
		{
			$name_part .= ";ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:";
			return explode("\n", $name_part.$qp_value_part);
		}else
		{
			$name_part .= ';CHARSET=UTF-8:';
		}
		return array($name_part.$value_part);
	}*/

	/**
	* Creates the vCard file.
	*
	* @param  array $records		Contains the array with database records.
	* @access private
	* @return boolean
	*/
	function _create_vcard($records) {
		$lines = array ();
		if (is_array($records)) {
			/*			if(count($records) > 1) {
							$lines[] = "BEGIN:VCARD";
						}*/
			foreach ($records as $record) {
				if ($this->_set_vcard($record, "db")) {
					foreach ($this->instance as $vcard) {
						//BEGIN:VCARD
						$lines[] = "BEGIN:VCARD";
						$lines[] = $this->version;

						foreach ($vcard as $property) {
							switch ($property->name) {
								case "N" :
									$name_part = $property->name;


									$value_part =
										$property->values[N_FAMILY].DELIM_SEMICOLON.
										$property->values[N_GIVEN].DELIM_SEMICOLON.
										$property->values[N_ADDITIONAL].DELIM_SEMICOLON.
										$property->values[N_PREFIX];


									$lines = array_merge($lines, String::format_vcard_line($name_part, $value_part, $this->add_leading_space_to_qp_encoded_line_wraps));


									$name_part = "FN";
									$value_part = $property->values[N_GIVEN].CHAR_WSP.$property->values[N_FAMILY];
									$lines = array_merge($lines, String::format_vcard_line($name_part, $value_part, $this->add_leading_space_to_qp_encoded_line_wraps));

									break;
								case "ADR" :
									//if (!empty ($property->values[ADR_STREET])) {
										$parm_types = "";
										foreach ($property->parm_types as $parm_type) {
											//$parm_types .= DELIM_SEMICOLON . PARM_TYPE . DELIM_EQUAL . $parm_type;
											$parm_types .= DELIM_SEMICOLON.$parm_type;
										}
										$name_part=$property->name.$parm_types;
										$value_part = DELIM_SEMICOLON.DELIM_SEMICOLON.
											$property->values[ADR_STREET].DELIM_SEMICOLON.
											$property->values[ADR_LOCALITY].DELIM_SEMICOLON.
											$property->values[ADR_REGION].DELIM_SEMICOLON.
											$property->values[ADR_POSTALCODE].DELIM_SEMICOLON.
											$property->values[ADR_COUNTRY];

										$lines = array_merge($lines, String::format_vcard_line($name_part, $value_part, $this->add_leading_space_to_qp_encoded_line_wraps));
									//}
									break;
								case "EMAIL" :
									//if (!empty ($property->values[0])) {
										$line = $property->name;

										foreach ($property->parm_types as $parm_type) {
											if(!empty($parm_type))
											{
												$line .= DELIM_SEMICOLON.$parm_type;
											}
										}

										$line .= DELIM_COLON.$property->values[0];
										$lines[] = $line;
									//}
									break;
								case "TEL" :
									//if (!empty ($property->values[0])) {
										$parm_types = "";
										foreach ($property->parm_types as $parm_type) {
											//											$parm_types .= DELIM_SEMICOLON . PARM_TYPE . DELIM_EQUAL . $parm_type;
											$parm_types .= DELIM_SEMICOLON.$parm_type;
										}
										$lines[] = $property->name.$parm_types.DELIM_COLON.$property->values[0];
									//}
									break;
								case "ORG" :
									//if (!empty ($property->values[ORG_NAME])) {

										$name_part = $property->name;
										$value_part = $property->values[ORG_NAME].DELIM_SEMICOLON.$property->values[ORG_UNIT];

										$lines = array_merge($lines, String::format_vcard_line($name_part, $value_part, $this->add_leading_space_to_qp_encoded_line_wraps));
									//}
									break;
								case "URL" :
									//if (!empty ($property->values[0])) {
										$parm_types = "";
										foreach ($property->parm_types as $parm_type) {
											//											$parm_types .= DELIM_SEMICOLON . PARM_TYPE . DELIM_EQUAL . $parm_type;
											$parm_types .= DELIM_SEMICOLON.$parm_type;
										}

										$lines[] = $property->name.$parm_types.DELIM_COLON.$property->values[0];
									//}
									break;
								case "BDAY" :

									//if (intval($property->values[0]) > 0) {
										$lines[] = $property->name.DELIM_COLON.$property->values[0];
										//$lines = array_merge($lines, String::format_vcard_line($property->name, $property->values[0], $this->add_leading_space_to_qp_encoded_line_wraps));
									//}
									break;
								default :
									if (!empty ($property->name)) {
										$lines = array_merge($lines, String::format_vcard_line($property->name, $property->values[0], $this->add_leading_space_to_qp_encoded_line_wraps));
									}
									break;
							/*	case 'X-GO-SALUTATION':
									if (!empty ($property->values[0])) {
										$lines = array_merge($lines, String::format_vcard_line($property->name, $property->values[0], $this->add_leading_space_to_qp_encoded_line_wraps));
									}
								break;*/
							}
						}
						$lines[] = $this->revision;
						//END:VCARD
						$lines[] = "END:VCARD";
					}
					unset ($this->instance);
				}
			}
			/*			if(count($records) > 1) {
							$lines[] = "END:VCARD";
						}*/
		}

		/*$this->vcf = '';
		foreach ($lines as $line) {
		 preg_match_all( '/.{1,73}([^=]{0,2})?/', $line, $matches);
		 $this->vcf .= implode( '=' . chr(13).chr(10), $matches[0] )."\r\n"; // add soft crlf's
		}*/
		$this->vcf = implode("\r\n", $lines)."\r\n";

		if (empty ($this->vcf)) {
			return false;
		}
		return true;
	}

	/**
	* Creates a vcard object.
	*
	* @param  array $content		Data for the vcf file.
	* @param  StringHelper $source		Contains information about the source the data comes from "db" or "file".
	* @access private
	* @return boolean
	*/
	function _set_vcard($content, $source) {
		if (!is_array($content)) {
			return false;
		}

		foreach ($content as $key => $value) {

			$property = new vcard_property();

			if ($source == "file") {
				$property->set($value);
				if (count($property->values)) {
					if (strtoupper($property->name) == 'BEGIN' && strtoupper($property->values[0]) == 'VCARD') {
						$this->index = isset ($this->index) ? $this->index + 1 : 0;
					}
				}
			} else {
				$property->set($value, $key);
				$this->index = 0;
			}

			$array_merged = false;
			if ($source == "db") {
				if ($property->name == "N" || $property->name == "ADR" || $property->name == "ORG") {
					for ($i = 0; $i < count($this->instance[$this->index]); $i ++) {
						if ($this->instance[$this->index][$i]->name == $property->name) {
							if ($this->instance[$this->index][$i]->parm_types[0] == $property->parm_types[0]) {
								$this->instance[$this->index][$i]->values = array_merge($this->instance[$this->index][$i]->values, $property->values);
								$array_merged = true;
							}
						}
					}
				}
			}
			if (!$array_merged) {
				$this->instance[$this->index][] = $property;
			}
			unset ($property);
		}
		return true;
	}

	/**
	* Sets the revision of the vCard.
	*
	* @param  void
	* @access private
	* @return void
	*/
	function _set_revision() {
		$date = date("Ymd", time());
		$time = gmdate("His", time());
		$this->revision = "REV".DELIM_COLON.$date.'T'.$time.'Z';
	}

	/**
	* Gets the street name from address.
	*
	* @param  string	$address Contains the address (street-name and house-number)
	* @access private
	* @return StringHelper
	*/
	function _get_address($address) {
		if (!$address = substr($address, 0, strrpos($address, " "))) {
			return CHAR_WSP;
		}

		return trim($address);
	}

	/**
	* Gets the house-number from address.
	*
	* @param  string	$address Contains the address (street-name and house-number)
	* @access private
	* @return StringHelper
	*/
	function _get_address_no($address) {
		if (!$address_no = strrchr($address, " ")) {
			return CHAR_WSP;
		}

		return trim($address_no);
	}
}

/**
* vCard property class containing methods and properties
* for creating of vCard properties.
*
* @package  addressbook vcf class
* @author   Georg Lorenz <georg@lonux.de>
* @since    Group-Office 2.06
*/
class vcard_property {
	var $group;
	var $name;
	var $parm_encoding;
	var $parm_language;
	var $parm_value;
	var $parm_charset;
	var $parm_types;
	var $values;

	function vcard_property() {
		$this->group = null;
		$this->name = null;
		$this->parm_encoding = null;
		$this->parm_language = null;
		$this->parm_value = null;
		$this->parm_charset = null;
		$this->parm_types = array ();
		$this->values = array ();
	}

	/**
	* Creates the property object.
	*
	* @param  StringHelper $value		Contains the property data.
	* @param  StringHelper $key		Contains the fieldname of the db record.
	* @access public
	* @return void
	*/
	function set($value, $key = "") {
		if (empty ($key)) { // file content
			if ($pos = strpos($value, DELIM_COLON)) {
				$left_part = trim(substr($value, 0, $pos));
				$right_part = trim(substr($value, $pos +1));
				$this->_set_name($left_part);
				$this->_set_parms($left_part);
				$this->_set_values($right_part);
			}
		} else { // db content
			$this->parm_types[0] = "";
			switch ($key) {
				case "first_name" :
					$this->name = "N";
					$this->values[N_GIVEN] = $value;
					break;
				case "middle_name" :
					$this->name = "N";
					$this->values[N_ADDITIONAL] = $value;
					break;
				case "last_name" :
					$this->name = "N";
					$this->values[N_FAMILY] = $value;
					break;
				case "title" :
					$this->name = "N";
					$this->values[N_PREFIX] = $value;
					break;
				case "birthday" :
					$this->name = "BDAY";
					$this->values[0] = (!empty($value) && $value != '0000-00-00') ? date("Ymd", strtotime($value)) : '';
					break;
				case "email" :
					$this->name = "EMAIL";
					$this->parm_types[0] = "INTERNET";
					//$this->parm_types[1] = "PREF";
					//$this->parm_types[1] = "HOME"; was set to home for blackberry
					$this->parm_types[1] = "";
					$this->values[0] = $value;
					break;
				case "email2":
					$this->name = "EMAIL";
					$this->parm_types[0] = "INTERNET";
					$this->parm_types[1] = "HOME";
					$this->values[0] = $value;
				break;
				case "email3":
					$this->name = "EMAIL";
					$this->parm_types[0] = "INTERNET";
					$this->parm_types[1] = "WORK";
					$this->values[0] = $value;
				break;
				case "function" :
					//$this->name = "ROLE";
					$this->name = "TITLE";
					$this->values[0] = $value;
					break;
				case "home_phone" :
					$this->name = "TEL";
					$this->parm_types[0] = "VOICE";
					$this->parm_types[1] = "HOME";
					$this->values[0] = $value;
					break;
				case "work_phone" :
					$this->name = "TEL";
					$this->parm_types[0] = "VOICE";
					$this->parm_types[1] = "WORK";
					$this->values[0] = $value;
					break;
				case "fax" :
					$this->name = "TEL";
					$this->parm_types[0] = "FAX";
					$this->parm_types[1] = "HOME";
					$this->values[0] = $value;
					break;
				case "work_fax" :
					$this->name = "TEL";
					$this->parm_types[0] = "FAX";
					$this->parm_types[1] = "WORK";
					$this->values[0] = $value;
					break;
				case "cellular" :
					$this->name = "TEL";
					$this->parm_types[0] = "CELL";
					$this->values[0] = $value;
					break;
				case "country" :
					$this->name = "ADR";
					$this->parm_types[0] = "HOME";
					$this->values[ADR_COUNTRY] = $value;
					break;
				case "state" :
					$this->name = "ADR";
					$this->parm_types[0] = "HOME";
					$this->values[ADR_REGION] = $value;
					break;
				case "city" :
					$this->name = "ADR";
					$this->parm_types[0] = "HOME";
					$this->values[ADR_LOCALITY] = $value;
					break;
				case "zip" :
					$this->name = "ADR";
					$this->parm_types[0] = "HOME";
					$this->values[ADR_POSTALCODE] = $value;
					break;
				case "address" :
					$this->name = "ADR";
					$this->parm_types[0] = "HOME";
					$this->values[ADR_STREET] = $value;
					break;
				case "comment" :
					$this->name = "NOTE";
					$this->values[0] = $value;
					break;
				case "department" :
					$this->name = "ORG";
					$this->values[ORG_UNIT] = $value;
					break;
				case "company_name" :
					$this->name = "ORG";
					$this->values[ORG_NAME] = $value;
					break;
				case "company_address" :
					$this->name = "ADR";
					$this->parm_types[0] = "WORK";
					$this->values[ADR_STREET] = $value;
					break;
				case "company_zip" :
					$this->name = "ADR";
					$this->parm_types[0] = "WORK";
					$this->values[ADR_POSTALCODE] = $value;
					break;
				case "company_city" :
					$this->name = "ADR";
					$this->parm_types[0] = "WORK";
					$this->values[ADR_LOCALITY] = $value;
					break;
				case "company_state" :
					$this->name = "ADR";
					$this->parm_types[0] = "WORK";
					$this->values[ADR_REGION] = $value;
					break;
				case "company_country" :
					$this->name = "ADR";
					$this->parm_types[0] = "WORK";
					$this->values[ADR_COUNTRY] = $value;
					break;
				case "company_homepage" :
					$this->name = "URL";
					$this->parm_types[0] = "WORK";
					$this->values[0] = $value;
					break;
				case "company_phone" :
					$this->name = GO_COMPANY_TEL;
					$this->values[0] = $value;
					break;
				case "company_fax" :
					$this->name = GO_COMPANY_FAX;
					$this->values[0] = $value;
					break;
				case "company_email" :
					$this->name = GO_COMPANY_EMAIL;
					$this->values[0] = $value;
					break;
				case "company_bank_no" :
					$this->name = GO_COMPANY_BANK_NO;
					$this->values[0] = $value;
					break;
				case "company_vat_no" :
					$this->name = GO_COMPANY_VAT_NO;
					$this->values[0] = $value;
					break;
				case "company_post_address" :
					$this->name = GO_COMPANY_POST_ADDRESS;
					$this->values[0] = $value;
					break;
				case "company_post_address_no" :
					$this->name = GO_COMPANY_POST_ADDRESS_NO;
					$this->values[0] = $value;
					break;
				case "company_post_city" :
					$this->name = GO_COMPANY_POST_CITY;
					$this->values[0] = $value;
					break;
				case "company_post_zip" :
					$this->name = GO_COMPANY_POST_ZIP;
					$this->values[0] = $value;
					break;
				case "company_post_state" :
					$this->name = GO_COMPANY_POST_STATE;
					$this->values[0] = $value;
					break;
				case "company_post_country" :
					$this->name = GO_COMPANY_POST_COUNTRY;
					$this->values[0] = $value;
					break;
				case 'salutation':
					$this->name = GO_SALUTATION;
					$this->values[0] = $value;
				break;
			}
		}
	}

	/**
	* Sets the name of the property.
	*
	* @param  StringHelper $text		Contains the property data.
	* @access private
	* @return void
	*/
	function _set_name($text) {
		/*
		//	we need the first element only
		//	it may contain a value e.g. ADR or A.ADR
		*/
		$arr=$this->_split($text, DELIM_SEMICOLON);
		$name_part = array_shift($arr);

		if ($pos = strpos($name_part, DELIM_DOT)) {
			$array = $this->_split($name_part, DELIM_DOT);
			$this->group = strtoupper(trim(array_shift($array)));
			$this->name = strtoupper(trim(array_shift($array)));
		} else {
			$this->name = strtoupper(trim($name_part));
		}
	}

	/**
	* Sets the parameters of the property.
	*
	* @param  StringHelper $text		Contains the property data.
	* @access private
	* @return void
	*/
	function _set_parms($text) {
		static $encodings = array ('7BIT', '8BIT', 'BASE64', 'QUOTED-PRINTABLE', 'B');
		static $values = array ('INLINE', 'CONTENT-ID', 'CID', 'URL', 'BINARY', 'PHONE-NUMBER', 'TEXT', 'URI', 'UTC-OFFSET', 'VCARD');

		$parm_array = $this->_split($text, DELIM_SEMICOLON);
		/*remove the first array value - it is the property name, we don't need it*/
		array_shift($parm_array);

		foreach ($parm_array as $array_value) {
			$parm = $this->_split($array_value, DELIM_COMMA);

			foreach ($parm as $value) {
				$parameter = $this->_split($value, DELIM_EQUAL);
				if (count($parameter) > 1) {
					switch (strtoupper(trim($parameter[0]))) {
						case PARM_ENCODING :
							$this->parm_encoding = strtoupper(trim($parameter[1]));
							break;
						case PARM_VALUE :
							$this->parm_value = strtoupper(trim($parameter[1]));
							break;
						case PARM_CHARSET :
							$this->parm_charset = strtoupper(trim($parameter[1]));
							break;
						case PARM_LANGUAGE :
							$this->parm_language = strtoupper(trim($parameter[1]));
							break;
						default :
							$this->parm_types[] = strtoupper(trim($parameter[1]));
							break;
					}
				} else {
					$parameter[0] = strtoupper(trim($parameter[0]));

					if (in_array($parameter[0], $encodings)) {
						$this->parm_encoding = $parameter[0];
					}
					elseif (in_array($parameter[0], $values)) {
						$this->parm_value = $parameter[0];
					} else {
						$this->parm_types[] = $parameter[0];
					}
				}
			}
		}
	}

	/**
	* Sets the values of the property.
	*
	* @param  StringHelper $text		Contains the property data.
	* @access private
	* @return void
	*/
	function _set_values($text) {
		switch ($this->parm_encoding) {
			case 'QUOTED-PRINTABLE' :
				$text = quoted_printable_decode($text);
				break;
			case 'BASE64' :
				$text = base64_decode($text);
				break;
			case 'B' :
				break;
			case '8BIT' :
				break;
			case '7BIT' :
				break;
		} // switch

		$values = $this->_split($text, DELIM_SEMICOLON);
		switch ($this->name) {
			case 'N' :
				$this->values[N_FAMILY] = isset ($values[0]) ? $values[0] : "";
				$this->values[N_GIVEN] = isset ($values[1]) ? $values[1] : "";
				$this->values[N_ADDITIONAL] = isset ($values[2]) ? $values[2] : "";
				$this->values[N_PREFIX] = isset ($values[3]) ? $values[3] : "";
				$this->values[N_SUFFIX] = isset ($values[4]) ? $values[4] : "";
				break;
			case 'ADR' :
				$this->values[ADR_POBOX] = isset ($values[0]) ? $values[0] : "";
				$this->values[ADR_EXTENDED] = isset ($values[1]) ? $values[1] : "";
				$this->values[ADR_STREET] = isset ($values[2]) ? $values[2] : "";
				$this->values[ADR_LOCALITY] = isset ($values[3]) ? $values[3] : "";
				$this->values[ADR_REGION] = isset ($values[4]) ? $values[4] : "";
				$this->values[ADR_POSTALCODE] = isset ($values[5]) ? $values[5] : "";
				$this->values[ADR_COUNTRY] = isset ($values[6]) ? $values[6] : "";
				break;
			case 'ORG' :
				$this->values[ORG_NAME] = isset ($values[0]) ? $values[0] : "";
				$this->values[ORG_UNIT] = isset ($values[1]) ? $values[1] : "";
				$this->values[ORG_OPTIONAL_UNIT] = isset ($values[2]) ? $values[2] : "";
				break;
			default :
				$this->values = $this->_split($text, DELIM_SEMICOLON);
				break;
		}
	}

	/**
	* Splits a string into an array by given delimiter.
	*
	* @param  StringHelper $text		Contains the property data.
	* @param  StringHelper $delimiter	Contains the delimiter.
	* @access private
	* @return array
	*/
	function _split($text, $delimiter) {
		switch ($delimiter) {
			case DELIM_SEMICOLON :
				$delimiter = '\\'.$delimiter;
				break;
			case DELIM_COMMA :
				$delimiter = '\\'.$delimiter;
				break;
			default :
				break;
		} // switch
		$regex = '/(?<!\\\\)('.$delimiter.')/i';
		$array = preg_split($regex, $text);

		return $array;
	}
}
?>
