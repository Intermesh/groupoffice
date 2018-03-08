<?php
	/** 
		* @copyright Copyright Boso d.o.o.
		* @author Mihovil Stanić <mihovil.stanic@boso.hr>
	*/
 
//Uncomment this line in new translations!
require($GO_LANGUAGE->get_fallback_language_file('cms'));

$lang['cms']['name']='Web stranice';
$lang['cms']['description']='Opis...';
$lang['cms']['site']='Stranica';
$lang['cms']['sites']='Stranice';
$lang['cms']['folder']='Direktorij';
$lang['cms']['folders']='Direktoriji';
$lang['cms']['file']='Datoteka';
$lang['cms']['files']='Datoteke';
$lang['cms']['template']='Predložak';
$lang['cms']['templates']='Predložci';
$lang['cms']['template_item']='Stavka predloška';
$lang['cms']['template_items']='Stavke predloška';

$lang['cms']['back']='Nazad';
$lang['cms']['message']='Poruka';
$lang['cms']['reset']='Resetiraj';
$lang['cms']['subject']='Predmet';
$lang['cms']['continue']='Nastavi';
$lang['cms']['cancel']='Otkaži';

$lang['cms']['error_email']='Unijeli ste neispravnu e-mail adresu';
$lang['cms']['sendmail_error']='Nije moguće poslati e-mail';
$lang['cms']['sendmail_success']='<h1>Hvala</h1>Poruka je uspješno poslana';
$lang['cms']['please_select']='Molimo izaberite';
$lang['cms']['cms_permissions']='Provjeravam dozvole CMS direktorija';
$lang['cms']['adding_share']='Dodajem dijeljenje za stranicu: ';
$lang['cms']['done_with_cms']='Done with CMS';

$lang['cms']['path_error']='Greška: could not resolve path: ';
$lang['cms']['include_file_error']='Greška: include_file requires path or file_id parameter';
$lang['cms']['cant_delete_site_treeview']='Ne možete izbrisati cijelu stranicu iz razgranatog pogleda. Izbrišite ju iz administracije stranice ako imate potrebne dozvole.';
$lang['cms']['cant_move_into_itself']='Can not move into itself';
$lang['cms']['done_with_cms']='Done with CMS';

$lang['cms']['antispam_fail']='Anti-spam odgovor je netočan. Molimo pokušajte ponovo.';
$lang['cms']['no_admin_rights']="Nemate administratorske ovlasti da mjenjate pristup korisnika direktorijima stranice.";

$lang['cms']['none'] = 'NIJEDAN';

$lang['cms']['template_not_found'] = 'Site template not found. Check if the site is plugged to a template and that the template\'s directory exists in modules/cms/template/...';