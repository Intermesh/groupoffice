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
 * @version $Id: cs.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Adresář';
$lang['addressbook']['description'] = 'Adresář slouží k uložení kontaktů a k jejich úpravě.';



$lang['addressbook']['allAddressbooks'] = 'Všechny Adresáře';
$lang['common']['addressbookAlreadyExists'] = 'Snažíte se vytvořit adresář, který již existuje';
$lang['addressbook']['notIncluded'] = 'Neimportovat';

$lang['addressbook']['comment'] = 'Komentář';
$lang['addressbook']['bankNo'] = 'Číslo účtu'; 
$lang['addressbook']['vatNo'] = 'Daň z přidané hodnoty';
$lang['addressbook']['contactsGroup'] = 'Skupina';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontakt';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Společnost';

$lang['addressbook']['customers'] = 'Zákazníci';
$lang['addressbook']['suppliers'] = 'Dodavatelé';
$lang['addressbook']['prospects'] = 'Perspektivy';


$lang['addressbook']['contacts'] = 'Kontakty';
$lang['addressbook']['companies'] = 'Společnosti';

$lang['addressbook']['newContactAdded']='Nový kontakt přidán';
$lang['addressbook']['newContactFromSite']='Nový kontakt byl přidán přes webový formulář.';
$lang['addressbook']['clickHereToView']='Klikni zde pro zobrazení kontaktu';

$lang['addressbook']['contactFromAddressbook']='Kontakt od %s';
$lang['addressbook']['companyFromAddressbook']='Společnost od %s';
$lang['addressbook']['defaultSalutation']='Milý/Milá [Pane/Paní] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Vybráno více adresářů';
$lang['addressbook']['incomplete_delete_contacts']='Nemáte oprávnění pro smazání všech vybraných kontaktů';
$lang['addressbook']['incomplete_delete_companies']='Nemáte oprávnění pro smazání všech vybraných společností';

$lang['addressbook']['emailAlreadyExists']='E-mailová adresa je již přidána k tomuto kontaktu';
$lang['addressbook']['emailDoesntExists']='E-mailová adresa nebyla nalezena';

$lang['addressbook']['imageNotSupported']='Nahraný typ obrázku není podporován. Podporovány jsou pouze obrázky typu: gif, png a jpg.';

?>
