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
 * @version $Id: nb.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Adressebok';
$lang['addressbook']['description'] = 'Modul for å håndtere alle kontakter.';



$lang['addressbook']['allAddressbooks'] = 'Alle adressebøker';
$lang['common']['addressbookAlreadyExists'] = 'Adresseboken du prøver å opprette eksisterer fra før.';
$lang['addressbook']['notIncluded'] = 'Ikke importer';

$lang['addressbook']['comment'] = 'Kommentar';
$lang['addressbook']['bankNo'] = 'Bankkonto'; 
$lang['addressbook']['vatNo'] = 'Org.nr.';
$lang['addressbook']['contactsGroup'] = 'Gruppe';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontaktperson';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Firma';

$lang['addressbook']['customers'] = 'Kunder';
$lang['addressbook']['suppliers'] = 'Leverandører';
$lang['addressbook']['prospects'] = 'Prospekter';


$lang['addressbook']['contacts'] = 'Kontaktpersoner';
$lang['addressbook']['companies'] = 'Firmaer';

$lang['addressbook']['newContactAdded']='Ny kontaktperson er lagt til';
$lang['addressbook']['newContactFromSite']='En ny kontaktperson er lagt til via et nettstedskjema.';
$lang['addressbook']['clickHereToView']='Trykk her for å vise kontakpersonen';
$lang['addressbook']['contactFromAddressbook']='Kontaktperson fra %s';
$lang['addressbook']['companyFromAddressbook']='Firma fra %s';

$lang['addressbook']['defaultSalutation']='Kjære [Hr./Fr.] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Flere adressebøker er valgt';
$lang['addressbook']['incomplete_delete_contacts']='Du har ikke rettigheter til å slette alle valgte kontaktpersoner';
$lang['addressbook']['incomplete_delete_companies']='Du har ikke rettigheter til å slette alle valgte firmaer';
$lang['addressbook']['emailAlreadyExists']='E-postadressen er allerede lagret på denne kontaktpersonen';
$lang['addressbook']['emailDoesntExists']='Fant ikke e-postadressen';
$lang['addressbook']['imageNotSupported']='Bildefilen du lastet opp er ikke støttet. Bare gif, png og jpg er gyldige filtyper.';
?>