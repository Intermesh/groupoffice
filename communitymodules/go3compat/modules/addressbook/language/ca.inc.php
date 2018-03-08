<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Contactes';
$lang['addressbook']['description'] = 'Mòdul per gestionar llibretes de contactes.';



$lang['addressbook']['allAddressbooks'] = 'Totes les llibretes';
$lang['common']['addressbookAlreadyExists'] = 'La llibreta que esteu intentant crear ja existeix';
$lang['addressbook']['notIncluded'] = 'No importar';

$lang['addressbook']['comment'] = 'Comentaris';
$lang['addressbook']['bankNo'] = 'Número de compte bancari'; 
$lang['addressbook']['vatNo'] = 'IVA';
$lang['addressbook']['contactsGroup'] = 'Grup';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contacte';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Empresa';

$lang['addressbook']['customers'] = 'Clients';
$lang['addressbook']['suppliers'] = 'Proveïdors';
$lang['addressbook']['prospects'] = 'Clients Potencials';


$lang['addressbook']['contacts'] = 'Contactes';
$lang['addressbook']['companies'] = 'Empreses';

$lang['addressbook']['newContactAdded']='Nou contacte agregat';
$lang['addressbook']['newContactFromSite']='Un nou contacte ha estat agregat a través del formulari Web';
$lang['addressbook']['clickHereToView']='Feu clic aquí per veure el contacte';

$lang['addressbook']['contactFromAddressbook']='Contacte de %s';
$lang['addressbook']['companyFromAddressbook']='Empresa de %s';
$lang['addressbook']['defaultSalutation']='Estimat/da [Sr./Sra.] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Selecció múltiple de llistats d\'adreces';
$lang['addressbook']['incomplete_delete_contacts']='No teniu permís per esborrar tots els contactes seleccionats';
$lang['addressbook']['incomplete_delete_companies']='No teniu permís per esborrar totes les companyies seleccionades';
$lang['addressbook']['emailAlreadyExists']='L\'adreça d\'e-mail ja està afegida en aquest contacte';
$lang['addressbook']['emailDoesntExists']='No s\'ha trobat l\'adreça d\'e-mail';
$lang['addressbook']['imageNotSupported']='La imatge que heu pujat no està suportada. Només es suporten imatges GIF, PNG i JPG.';
?>
