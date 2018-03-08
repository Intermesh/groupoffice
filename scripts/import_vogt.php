<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

ini_set('max_execution_time', 0);

require('../www/Group-Office.php');

require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();

//login as admin
$GLOBALS['GO_SECURITY']->logged_in($GO_USERS->get_user(1));
$GLOBALS['GO_MODULES']->load_modules();

require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
$GO_LINKS = new GO_LINKS();

error_reporting(E_ALL ^ E_DEPRECATED);

$del = ',';
$enc = '"';

//We'll import custom fields to this category
$cf_category_name = 'Import';
$addressbook_name = 'Import';

$dir = '/home/sjmeut/Desktop/';

require_once($GLOBALS['GO_MODULES']->modules['projects']['class_path'] . 'projects.class.inc.php');
$pm = new projects();

require_once($GLOBALS['GO_MODULES']->modules['billing']['class_path'] . 'billing.class.inc.php');
$bs = new billing();

require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'] . 'addressbook.class.inc.php');
$ab = new addressbook();

require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'] . 'customfields.class.inc.php');
$cf = new customfields();


$cf_fieldmap = array();
//create custom fields with category and create a map
function create_custom_fields($type, $cf_category_name, $custom_fields) {
	global $cf_fieldmap, $cf, $GO_SECURITY;
	//create custom fields category
	$category = $cf->get_category_by_name($type, $cf_category_name);
	if (!$category) {
		$category['name'] = $cf_category_name;
		$category['type'] = $type;
		$category['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl();
		$category_id = $cf->add_category($category);
	} else {
		$category_id = $category['id'];
	}

	$cf_fieldmap[$type] = array();

	foreach ($custom_fields as $f) {
		$field = $cf->get_field_by_name($category_id, $f);
		if (!$field) {
			$field = array('name' => $f, 'datatype' => 'text', 'category_id' => $category_id);
			$cf_fieldmap[$type][$f] = 'col_' . $cf->add_field($field);
		} else {
			$cf_fieldmap[$type][$f] = 'col_' . $field['id'];
		}
	}
	return $category_id;
}


create_custom_fields(5, $cf_category_name, array('ProjectNummer', 'ProjectAdres', 'ProjectPostcode', 'ProjectPlaats', 'ProjectLand', 'ProjectTelefoonnummer', 'Projectleider'));

if(true)
{        
        $pm->get_default_type();
        $project_type_id = $pm->f('id');

        //map the std fields to the csv file headers
	$std_fieldmap['ProjectNummer'] = 'id';
	$std_fieldmap['ProjectNaam'] = 'name';
	$std_fieldmap['ProjectStatus'] = 'status';

	File::convert_to_utf8($dir . '/projecten.csv');

	//Import projects
	$fp = fopen($dir . '/projecten.csv', "r");
	if (!$fp)
		die('Failed to open projects file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from projects file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++)
        {
		$index_map[$i] = $headers[$i];
	}

	while ($record = fgetcsv($fp, null, $del, $enc)) {
		try {
                    
			$project = array('type_id' => $project_type_id, 'user_id' => 1, 'parent_project_id' => 0);
			$cf_values = array();

                        $project_name = '';
			for ($i = 0, $m = count($record); $i < $m; $i++) {                                
				$field = $index_map[$i];

				if (isset($std_fieldmap[$field]))
                                {
                                        switch($i)
                                        {
                                                case 0:
                                                        $project_name = $record[$i];
                                                        break;
                                                case 1:
                                                        $project[$std_fieldmap[$field]] = $project_name.' '.$record[$i];
                                                        break;
                                                default:
                                                        $project[$std_fieldmap[$field]] = $record[$i];
                                                        break;                                        
                                        }
				}
                                if (isset($cf_fieldmap[5][$field]))
                                {
					$cf_values[$cf_fieldmap[5][$field]] = $record[$i];
				}
			}                                                

			if(isset($project['name']))
                        {
				echo "Importing " . $project['name'] . "\n";

                                $status = $pm->get_status_by_name($project['status']);
                                if($status)
                                {
                                        $project['status_id'] = $status['id'];
                                }else
                                {
                                        $project['status_id'] = $pm->add_status(array('name' => $project['status']));
                                        
                                }
                                unset($project['status']);

                                $project_id = $pm->add_project($project);                                
                                $cf_values['link_id'] = $project_id;
				$cf->replace_row('cf_5', $cf_values);
			}else
                        {
				echo "No project name found. Skipping:" . var_export($project, true) . "\n\n";
			}                     
		} catch (Exception $e) {

		}
	}
	fclose($fp);
}


create_custom_fields(3, $cf_category_name, array('Bedrijfsnummer'));

$addressbook = $ab->get_addressbook_by_name($addressbook_name);
if (!$addressbook) {
        $addressbook = $ab->add_addressbook(1, $addressbook_name);
}
$addressbook_id = $addressbook['id'];
        
if(true)
{                      
        //map the std fields to the csv file headers
        $std_fieldmap = array();
	$std_fieldmap['Bedrijf'] = 'name';
	$std_fieldmap['Adres'] = 'address';
        $std_fieldmap['Postcode'] = 'zip';
        $std_fieldmap['Plaats'] = 'city';
        $std_fieldmap['Provincie'] = 'state';
        $std_fieldmap['Land/regio'] = 'country';
        $std_fieldmap['Postbus'] = 'post_address';
        $std_fieldmap['PostbusPostcode'] = 'post_zip';
        $std_fieldmap['PostbusPlaats'] = 'post_city';
        $std_fieldmap['Telefoonnummer'] = 'phone';
        $std_fieldmap['Faxnummer'] = 'fax';
        $std_fieldmap['E-mail'] = 'email';
        $std_fieldmap['Website'] = 'homepage';


	File::convert_to_utf8($dir . '/bedrijven.csv');

	//Import projects
	$fp = fopen($dir . '/bedrijven.csv', "r");
	if (!$fp)
		die('Failed to open companies file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from companies file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++)
        {
		$index_map[$i] = $headers[$i];
	}

	while ($record = fgetcsv($fp, null, $del, $enc)) {
		try {

			$company = array('addressbook_id' => $addressbook_id, 'user_id' => 1, 'email_allowed' => 1);
			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($std_fieldmap[$field]))
                                {
                                        if(($field == 'Postbus') && $record[$i])
                                        {
                                                $company[$std_fieldmap[$field]] = 'Postbus '.$record[$i];
                                        }else
                                        {
                                                $company[$std_fieldmap[$field]] = $record[$i];
                                        }
				}
                                if (isset($cf_fieldmap[3][$field]))
                                {
					$cf_values[$cf_fieldmap[3][$field]] = $record[$i];
				}
			}

			if(isset($company['name']))
                        {                                
				echo "Importing " . $company['name'] . "\n";

                                if(!$company['post_address'])
                                {
                                    $company['post_address'] = $company['address'];
                                    $company['post_zip'] = $company['zip'];
                                    $company['post_city'] = $company['city'];
                                }

                                require_once($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));

                                $arr = array_keys($countries, $company['country']);                                
                                $company['country'] = isset($arr[0]) ? $arr[0] : $company['country'];

                                $company['post_country'] = $company['country'];

                                $company_id = $ab->add_company($company);
                                $cf_values['link_id'] = $company_id;
				$cf->replace_row('cf_3', $cf_values);
			}else
                        {
				echo "No company name found. Skipping:" . var_export($company, true) . "\n\n";
			}
		} catch (Exception $e) {

		}
	}
	fclose($fp);
}


create_custom_fields(7, $cf_category_name, array('Leverancier', 'Projectnummer', 'ProjectFase', 'Afzender', 'Omschrijving', 'DatumGoedgekeurd', 'DatumLeveringGewenst', 'DatumBesteld', 'DatumLeveringAfgesproken', 'Productgroep', 'DatumGeleverd', 'Onderdeel', 'Orderbevestiging', 'Tijdelijknummer'));

if(true)
{        
        $book = $bs->get_default_purchase_orders_book();
        if($book)
        {
                $book_id = $book['id'];
        }else
        {
                $book_id = $bs->add_book(array('name' => 'Bestelbonnen', 'default_vat' => 19, 'is_purchase_orders_book' => 1, 'country' => 'NL', 'currency' => '€', 'order_id_prefix' => '%y-', 'user_id' => 1, 'acl_id' => $GLOBALS['GO_SECURITY']->get_new_acl('book')));
        }
       
        //map the std fields to the csv file headers
        $std_fieldmap = array();
        $std_fieldmap['Id'] = 'id';
        $std_fieldmap['BestelNummer'] = 'order_id';
	$std_fieldmap['DatumAanmaak'] = 'mtime';
        $std_fieldmap['InkoopOpdrachtNummer'] = 'po_id';
        $std_fieldmap['Status'] = 'status';
        $std_fieldmap['PrijsExclBTW'] = 'subtotal';
	$std_fieldmap['PrijsInclBTW'] = 'total';

	File::convert_to_utf8($dir . '/inkoopopdrachten.csv');

	//Import projects
	$fp = fopen($dir . '/inkoopopdrachten.csv', "r");
	if (!$fp)
		die('Failed to open orders file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from orders file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++)
        {
		$index_map[$i] = $headers[$i];
	}
        
        $order_mtime = time();
	while ($record = fgetcsv($fp, null, $del, $enc)) {
		try {
                        $cf_values = array();
			$order = array('book_id' => $book_id, 'user_id' => 1, 'mtime' => $order_mtime, 'project_id' => 0, 'customer_salutation' => 'Beste heer/mevrouw');

			for ($i = 0, $m = count($record); $i < $m; $i++) {                                                            
				$field = $index_map[$i];                                                                
				if (isset($std_fieldmap[$field]))
                                {                                    
                                        if($field == 'DatumAanmaak')
                                        {
                                                
                                                $order[$std_fieldmap[$field]] = strtotime($record[$i]);
                                        }else
                                        if($field == 'PrijsExclBTW' || $field == 'PrijsInclBTW')
                                        {                                                                                              
                                                $num = ereg_replace("[^0-9.,]", "", $record[$i]);
                                                $num = str_replace(',','', $num);
                                                $order[$std_fieldmap[$field]] = $num;
                                        }else
                                        {
                                                $order[$std_fieldmap[$field]] = $record[$i];
                                        }
				}
                                if (isset($cf_fieldmap[7][$field]))
                                {
                                        $cf_values[$cf_fieldmap[7][$field]] = $record[$i];
				}
			}

			if(isset($order['id']))
                        {
				echo "Importing " . $order['id'] . "\n";
                                unset($order['id']);

                                if($order['status'])
                                {
                                        $status = $bs->get_order_status_by_name($book_id, $order['status']);
                                        if($status)
                                        {
                                                $order['status_id'] = $status['id'];
                                        }else
                                        {
                                                $status_language['status_id'] = $order['status_id'] = $bs->add_order_status(array('book_id' => $book_id, 'acl_id' => $GLOBALS['GO_SECURITY']->get_new_acl('order_status')));

                                                if($status_language['status_id'])
                                                {
                                                        $status_language['language_id']=1;
                                                        $status_language['name']=$order['status'];
                                                        $bs->add_status_language($status_language);
                                                }
                                        }
                                        unset($order['status']);
                                }

                                $search_query=$cf_values[$cf_fieldmap[7]['Projectnummer']];
                                $search_field=$cf_fieldmap[5]['ProjectNummer'];
                                if($pm->get_projects('name', 'ASC', 0, 0, $search_field, $search_query, true))
                                {
                                        $project = $pm->next_record();                                        
                                        $order['project_id'] = $project['id'];
                                }

                                $company_name = $cf_values[$cf_fieldmap[7]['Leverancier']];
                                $existing_company = $ab->get_company_by_name($addressbook_id, $company_name);
                                if($existing_company)
                                {
                                        require($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));
                                        
                                        $order['company_id'] = $existing_company['id'];
                                        $order['customer_to'] = $order['customer_name'] = $existing_company['name'];                                        
                                        $order['customer_address'] = $existing_company['address'];
                                        $order['customer_zip'] = $existing_company['zip'];
                                        $order['customer_city'] = $existing_company['city'];
                                        
                                        $arr = array_keys($countries, $existing_company['country']);
                                        $order['customer_country'] = isset($arr[0]) ? $arr[0] : $existing_company['country'];
                                        
                                        $order['customer_email'] = $existing_company['email'];
                                }else
                                if($company_name)
                                {
                                        $order['company_id'] = $ab->add_company(array('addressbook_id' => $addressbook_id, 'name' => $company_name));
                                        $order['customer_to'] = $order['customer_name'] = $company_name;
                                }else
                                {
                                        $order['customer_to'] = $order['customer_name'] = '';
                                }
                                
                                $order['btime'] = $order['mtime'];
                                $order['vat'] = $order['total'] - $order['subtotal'];

                                $order_id = $bs->add_order($order);
                                $cf_values['link_id'] = $order_id;
				$cf->replace_row('cf_7', $cf_values);

                                if($order['project_id'])
                                {
                                        $GO_LINKS->add_link($order['project_id'], 5, $order_id, 7);
                                }

                                if(isset($order['company_id']))
                                {
                                        $GO_LINKS->add_link($order['company_id'], 3, $order_id, 7);
                                }
                                
			}else
                        {
				echo "No order id found. Skipping:" . var_export($order, true) . "\n\n";
			}
		} catch (Exception $e) {

		}
	}
	fclose($fp);
}


?>
