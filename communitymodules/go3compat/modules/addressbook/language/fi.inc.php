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
 * @version $Id: fi.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Osoitekirja';
$lang['addressbook']['description'] = 'Moduuli, jolla ylläpidetään kontakteja.';



$lang['addressbook']['allAddressbooks'] = 'Kaikki osoitekirjat';
$lang['common']['addressbookAlreadyExists'] = 'Osoitekirja, jota yrität luoda on jo olemassa';
$lang['addressbook']['notIncluded'] = 'Älä tuo';

$lang['addressbook']['comment'] = 'Kommentti';
$lang['addressbook']['bankNo'] = 'Pankkiyhteys'; 
$lang['addressbook']['vatNo'] = 'ALV-tunnus';
$lang['addressbook']['contactsGroup'] = 'Ryhmä';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kontakti';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Yritys';

$lang['addressbook']['customers'] = 'Asiakkaat';
$lang['addressbook']['suppliers'] = 'Toimittajat';
$lang['addressbook']['prospects'] = 'Prospektit';


$lang['addressbook']['contacts'] = 'Kontaktit';
$lang['addressbook']['companies'] = 'Yritykset';

$lang['addressbook']['newContactAdded']='Uusi kontakti lisätty';
$lang['addressbook']['newContactFromSite']='Uusi kontakti lisätty websivun lomakkeen kautta.';
$lang['addressbook']['clickHereToView']='Klikkaa nähdäksesi kontaktin tiedot';
?>