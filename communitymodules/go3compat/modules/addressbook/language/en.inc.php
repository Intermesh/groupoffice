<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of {product_name}. You should have received a copy of the
 * {product_name} license along with {product_name}. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: en.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
//require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Addressbook';
$lang['addressbook']['description'] = 'Module to manage all contacts.';



$lang['addressbook']['allAddressbooks'] = 'All Addressbooks';
$lang['common']['addressbookAlreadyExists'] = 'The addressbook you are trying to create already exists';
$lang['addressbook']['notIncluded'] = 'Do not import';

$lang['addressbook']['comment'] = 'Comment';
$lang['addressbook']['bankNo'] = 'Bank number'; 
$lang['addressbook']['vatNo'] = 'VAT number';
$lang['addressbook']['contactsGroup'] = 'Group';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contact';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Company';

$lang['addressbook']['customers'] = 'Customers';
$lang['addressbook']['suppliers'] = 'Suppliers';
$lang['addressbook']['prospects'] = 'Prospects';


$lang['addressbook']['contacts'] = 'Contacts';
$lang['addressbook']['companies'] = 'Companies';

$lang['addressbook']['newContactAdded']='New contact added';
$lang['addressbook']['newContactFromSite']='A new contact was added through a website form.';
$lang['addressbook']['clickHereToView']='Click here to view the contact';

$lang['addressbook']['contactFromAddressbook']='Contact from %s';
$lang['addressbook']['companyFromAddressbook']='Company from %s';
$lang['addressbook']['defaultSalutation']='Dear [Mr./Mrs.] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Multiple addressbooks selected';
$lang['addressbook']['incomplete_delete_contacts']='You don\'t have permission to delete all selected contacts';
$lang['addressbook']['incomplete_delete_companies']='You don\'t have permission to delete all selected companies';

$lang['addressbook']['emailAlreadyExists']='E-mail aadress is already added to this contact';
$lang['addressbook']['emailDoesntExists']='E-mail address wasn\'t found';

$lang['addressbook']['imageNotSupported']='The image you uploaded is not supported. Only gif, png and jpg images are supported.';

$lang['addressbook']['no_addressbook_id'] = 'Invalid addressbook id has been passed with the request to the server!';
$lang['addressbook']['undefined'] = '-';
?>