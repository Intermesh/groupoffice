<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Contactos';
$lang['addressbook']['description'] = 'Módulo para gestionarlibretas de contactos.';



$lang['addressbook']['allAddressbooks'] = 'Todas las libretas';
$lang['common']['addressbookAlreadyExists'] = 'La libreta que está intentando crear ya existe';
$lang['addressbook']['notIncluded'] = 'No importar';

$lang['addressbook']['comment'] = 'Comentarios';
$lang['addressbook']['bankNo'] = 'Número de cuenta bancaria'; 
$lang['addressbook']['vatNo'] = 'IVA';
$lang['addressbook']['contactsGroup'] = 'Grupo';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contacto';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Empresa';

$lang['addressbook']['customers'] = 'Clientes';
$lang['addressbook']['suppliers'] = 'Proveedores';
$lang['addressbook']['prospects'] = 'Potenciales clientes';


$lang['addressbook']['contacts'] = 'Contactos';
$lang['addressbook']['companies'] = 'Empresas';

$lang['addressbook']['newContactAdded']='Contacto nuevo agregado';
$lang['addressbook']['newContactFromSite']='Un nuevo contacto fue agregado a través del formulario web';
$lang['addressbook']['clickHereToView']='Haga click aca para ver el contacto';

$lang['addressbook']['contactFromAddressbook']='Contacto de %s';
$lang['addressbook']['companyFromAddressbook']='Empresa de %s';
$lang['addressbook']['defaultSalutation']='Estimado/a [Sr./Sra.] {middle_name} {last_name}';
?>
