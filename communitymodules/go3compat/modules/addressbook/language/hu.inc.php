<?php

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));

$lang['addressbook']['name'] = 'Címjegyzék';
$lang['addressbook']['description'] = 'Modul a kapcsolatok kezeléséhez.';

$lang['addressbook']['allAddressbooks'] = 'Minden címjegyzék';
$lang['common']['addressbookAlreadyExists'] = 'A címjegyzék, amit próbáltál létrehozni már létezik.';
$lang['addressbook']['notIncluded'] = 'Do not import';

$lang['addressbook']['comment'] = 'Megjegyzés';
$lang['addressbook']['bankNo'] = 'Bankszámlaszám'; 
$lang['addressbook']['vatNo'] = 'Adószám';
$lang['addressbook']['contactsGroup'] = 'Csoport';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Kapcsolat';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Cég';

$lang['addressbook']['customers'] = 'Ügyfelek';
$lang['addressbook']['suppliers'] = 'Suppliers';
$lang['addressbook']['prospects'] = 'Prospects';


$lang['addressbook']['contacts'] = 'kapcsolatok';
$lang['addressbook']['companies'] = 'Companies';

$lang['addressbook']['newContactAdded']='Új kapcsolat hozzáadva';
$lang['addressbook']['newContactFromSite']='A new contact was added through a website form.';
$lang['addressbook']['clickHereToView']='Click here to view the contact';
?>