<?php
/////////////////////////////////////////////////////////////////////////////////
//
// Copyright Intermesh
// 
// This file is part of {product_name}. You should have received a copy of the
// {product_name} license along with {product_name}. See the file /LICENSE.TXT
// 
// If you have questions write an e-mail to info@intermesh.nl
//
// @copyright Copyright Intermesh
// @version $Id: fr.inc.php 20766 2017-01-05 13:32:36Z mschering $
// @author Merijn Schering <mschering@intermesh.nl>
//
// French Translation
// Version : 4.0.99
// Author : Lionel JULLIEN / Boris HERBINIERE-SEVE
// Date : September, 20 2012
//
/////////////////////////////////////////////////////////////////////////////////

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));

$lang['addressbook']['name'] = 'Carnet d\'adresses';
$lang['addressbook']['description'] = 'Module de gestion des contacts.';



$lang['addressbook']['allAddressbooks'] = 'Tous les carnets d\'adresses';
$lang['common']['addressbookAlreadyExists'] = 'Le carnet d\'adresses que vous essayez de créer existe déjà';
$lang['addressbook']['notIncluded'] = 'Ne pas importer';

$lang['addressbook']['comment'] = 'Commentaire';
$lang['addressbook']['bankNo'] = 'Numéro de banque'; 
$lang['addressbook']['vatNo'] = 'Numéro de TVA';
$lang['addressbook']['contactsGroup'] = 'Groupe';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contact';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Société';

$lang['addressbook']['customers'] = 'Clients';
$lang['addressbook']['suppliers'] = 'Fournisseurs';
$lang['addressbook']['prospects'] = 'Prospects';


$lang['addressbook']['contacts'] = 'Contacts';
$lang['addressbook']['companies'] = 'Sociétés';

$lang['addressbook']['newContactAdded']='Nouveau contact ajouté';
$lang['addressbook']['newContactFromSite']='Un nouveau contact a été ajouté via le formulaire du site web.';
$lang['addressbook']['clickHereToView']='Cliquez ici pour voir le contact';

$lang['addressbook']['contactFromAddressbook']='Contact de %s';
$lang['addressbook']['companyFromAddressbook']='Société de %s';
$lang['addressbook']['defaultSalutation']='[Mr./Mme.] {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Plusieurs carnets d\'adresses sélectionnés';
$lang['addressbook']['incomplete_delete_contacts']='Vous n\'avez pas le droit de supprimer tous les contacts sélectionnés';
$lang['addressbook']['incomplete_delete_companies']='Vous n\'avez pas le droit de supprimer toutes les sociétés sélectionnées';

$lang['addressbook']['emailAlreadyExists']='Cette adresse électronique est déjà ajoutée a ce contact';
$lang['addressbook']['emailDoesntExists']='L\'adresse électronique n\'a pas été trouvée';

$lang['addressbook']['imageNotSupported']='Le format de l\'image transférée n\'est pas supporté. Veuillez utiliser une image au format gif, png ou jpg.';

$lang['addressbook']['no_addressbook_id']= 'Un id de carnet d\'adresse invalide a été passé avec la requête!';
$lang['addressbook']['undefined']= '-';
?>
