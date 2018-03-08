<?php
	/** 
		* @copyright Copyright Boso d.o.o.
		* @author Mihovil Stanić <mihovil.stanic@boso.hr>
	*/
 
//Uncomment this line in new translations!
require($GO_LANGUAGE->get_fallback_language_file('addressbook'));

$lang['addressbook']['name'] = 'Adresar';
$lang['addressbook']['description'] = 'Modul za upravljanje kontaktima.';



$lang['addressbook']['allAddressbooks'] = 'Svi adresari';
$lang['common']['addressbookAlreadyExists'] = 'Adresar koji želite napraviti već postoji';
$lang['addressbook']['notIncluded'] = 'Nemoj uvoziti';

$lang['addressbook']['comment'] = 'Komentar';
$lang['addressbook']['bankNo'] = 'Bankovni račun'; 
$lang['addressbook']['vatNo'] = 'OIB broj';
$lang['addressbook']['contactsGroup'] = 'Grupa';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontakt';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Tvrtka';

$lang['addressbook']['customers'] = 'Kupci';
$lang['addressbook']['suppliers'] = 'Dobavljači';
$lang['addressbook']['prospects'] = 'Potencijalni';


$lang['addressbook']['contacts'] = 'Kontakti';
$lang['addressbook']['companies'] = 'Tvrtke';

$lang['addressbook']['newContactAdded']='Dodan je novi kontakt';
$lang['addressbook']['newContactFromSite']='Novi kontakt je dodan preko formulara na web stranici.';
$lang['addressbook']['clickHereToView']='Kliknite ovdje kako bi ste vidjeli kontakt';

$lang['addressbook']['contactFromAddressbook']='Kontakt od %s';
$lang['addressbook']['companyFromAddressbook']='Tvrtka od %s';
$lang['addressbook']['defaultSalutation']='Dragi [gospodine/gospođo] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Više adresara odabrano';
$lang['addressbook']['incomplete_delete_contacts']='Nemate odobrenje da obrišete sve odabrane kontakte';
$lang['addressbook']['incomplete_delete_companies']='Nemate odobrenje da obrišete sve odabrane tvrtke';

$lang['addressbook']['emailAlreadyExists']='E-mail adresa je već dodana ovom kontaktu';
$lang['addressbook']['emailDoesntExists']='E-mail adresa nije pronađena';

$lang['addressbook']['imageNotSupported']='Slika koju ste prenjeli nije podržana. Samo gif, png i jpg slike su podržane.';
?>