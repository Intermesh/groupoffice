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
 * @version $Id: pl.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Polish Translation v1.0
//Author : Robert GOLIAT info@robertgoliat.com  info@it-administrator.org
//Date : January, 20 2009
//Polish Translation v1.1
//Author : Paweł Dmitruk pawel.dmitruk@gmail.com
//Date : September, 05 2010
//Polish Translation v1.2
//Author : rajmund
//Date : January, 26 2011

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));

$lang['addressbook']['name'] = 'Książka adresowa';
$lang['addressbook']['description'] = 'Moduł do zarządzania wszystkimi kontaktami.';



$lang['addressbook']['allAddressbooks'] = 'Wszystkie książki adresowe';
$lang['common']['addressbookAlreadyExists'] = 'Książka adresowa, którą próbujesz utworzyć juz istnieje';
$lang['addressbook']['notIncluded'] = 'Nie importuj';

$lang['addressbook']['comment'] = 'Uwagi';
$lang['addressbook']['bankNo'] = 'Nr konta bankowego'; 
$lang['addressbook']['vatNo'] = 'NIP';
$lang['addressbook']['contactsGroup'] = 'Grupa';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontakt';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Firma';

$lang['addressbook']['customers'] = 'Klienci';
$lang['addressbook']['suppliers'] = 'Dostawcy';
$lang['addressbook']['prospects'] = 'Przyszłościowi';


$lang['addressbook']['contacts'] = 'Kontakty';
$lang['addressbook']['companies'] = 'Firmy';

$lang['addressbook']['newContactAdded']='Nowy kontakt został dodany';
$lang['addressbook']['newContactFromSite']='Nowy kontakt został dodany za pomocą formularza www.';
$lang['addressbook']['clickHereToView']='Kliknij tutaj by obejrzeć kontakt';
$lang['addressbook']['contactFromAddressbook']='Kontakt z %s';
$lang['addressbook']['companyFromAddressbook']='Firma z %s';
$lang['addressbook']['defaultSalutation']='Szanowny [Pan/Pani] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Wybrano wiele książek adresowych';
$lang['addressbook']['incomplete_delete_contacts']='Nie masz uprawnień do usunięcia wszystkich wybranych kontaktów';
$lang['addressbook']['incomplete_delete_companies']='Nie masz uprawnień do usunięcia wszystkich wybranych firm';
$lang['addressbook']['emailAlreadyExists']='Adres e-mail został już dodany do tego kontaktu';
$lang['addressbook']['emailDoesntExists']='Adres e-mail nie został znaleziony';
$lang['addressbook']['imageNotSupported']='Typ przesłanego obrazu nie jest obsługiwany. Obsługiwane formaty to: gif, png oraz jpg.';
?>