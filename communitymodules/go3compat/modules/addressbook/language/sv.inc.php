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
 * @version $Id: sv.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Adressbok';
$lang['addressbook']['description'] = 'Modul för att hantera kontakter.';



$lang['addressbook']['allAddressbooks'] = 'Alla adressböcker';
$lang['common']['addressbookAlreadyExists'] = 'Den adressbok du försöker skapa finns redan';
$lang['addressbook']['notIncluded'] = 'Importera inte';

$lang['addressbook']['comment'] = 'Kommentar';
$lang['addressbook']['bankNo'] = 'Banknummer'; 
$lang['addressbook']['vatNo'] = 'Momsreg.nummer';
$lang['addressbook']['contactsGroup'] = 'Grupp';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontakt';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Företag';

$lang['addressbook']['customers'] = 'Kunder';
$lang['addressbook']['suppliers'] = 'Leverantörer';
$lang['addressbook']['prospects'] = 'Övriga';


$lang['addressbook']['contacts'] = 'Kontakter';
$lang['addressbook']['companies'] = 'Företag';

$lang['addressbook']['newContactAdded']= 'Ny kontakt tillagd';
$lang['addressbook']['newContactFromSite']= 'En ny kontakt har lagts till via ett webb-formulär.';
$lang['addressbook']['clickHereToView']= 'Klicka här för att visa kontakten';

$lang['addressbook']['contactFromAddressbook']='Kontakt från %s';
$lang['addressbook']['companyFromAddressbook']='Företag från %s';
$lang['addressbook']['defaultSalutation']='Bästa [Herr/Fru] {first_name} {last_name}';

$lang['addressbook']['multipleSelected']='Flera addressböcker valda';
$lang['addressbook']['incomplete_delete_contacts']='Du har inte behörighet att radera alla valda kontakter';
$lang['addressbook']['incomplete_delete_companies']='Du har inte behörighet att radera alla valda företag';

$lang['addressbook']['emailAlreadyExists']='Denna kontakt har redan en e-postadress';
$lang['addressbook']['emailDoesntExists']='E-postadressen hittades inte';

$lang['addressbook']['imageNotSupported']='Bilden som laddades upp stöds inte. Bara gif-, png- och jpg-bilder går att använda.';
?>
