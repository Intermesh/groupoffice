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
 * @version $Id: nl.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Adresboek';
$lang['addressbook']['description'] = 'Module om alle contacten te beheren.';

$lang['addressbook']['allAddressbooks'] = 'Alle Adresboeken';
$lang['common']['addressbookAlreadyExists'] = 'Het adresboek wat je probeert te maken bestaat al';
$lang['addressbook']['notIncluded'] = 'Niet importeren';

$lang['addressbook']['comment'] = 'Opmerking';
$lang['addressbook']['bankNo'] = 'Bankrekeningnummer'; 
$lang['addressbook']['vatNo'] = 'BTW-nummer';
$lang['addressbook']['contactsGroup'] = 'Groep';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contact';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Bedrijf';

$lang['addressbook']['customers'] = 'Klanten';
$lang['addressbook']['suppliers'] = 'Leveranciers';
$lang['addressbook']['prospects'] = 'Potentiële klanten';

$lang['addressbook']['contacts']= 'Contactpersonen';
$lang['addressbook']['companies']= 'Bedrijven';

$lang['addressbook']['newContactAdded']='Nieuw contactpersoon toegevoegd';
$lang['addressbook']['newContactFromSite']='Een nieuw contactpersoon was via een websiteformulier toegevoegd';
$lang['addressbook']['clickHereToView']='Klik hier om de contactpersoon te bekijken';

$lang['addressbook']['contactFromAddressbook']='Contactpersoon uit %s';
$lang['addressbook']['companyFromAddressbook']='Bedrijf uit %s';

$lang['addressbook']['defaultSalutation']='Geachte [heer/mevrouw] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Meerdere adresboeken geselecteerd';
$lang['addressbook']['incomplete_delete_contacts']='U heeft niet voldoende rechten om alle geselecteerde contacten te verwijderen';
$lang['addressbook']['incomplete_delete_companies']='U heeft niet voldoende rechten om alle geselecteerde bedrijven te verwijderen';

$lang['addressbook']['emailAlreadyExists']='E-mail adres is al toegevoegd aan deze contactpersoon';
$lang['addressbook']['emailDoesntExists']='E-mail adres is niet gevonden';

$lang['addressbook']['imageNotSupported']='De afbeelding die u stuurde wordt niet ondersteund. Alleen gif, png en jpg afbeeldingen worden ondersteund.';

$lang['addressbook']['no_addressbook_id'] = 'Onjuiste adresboek-id werd naar de server gestuurd!';
$lang['addressbook']['undefined'] = '-';