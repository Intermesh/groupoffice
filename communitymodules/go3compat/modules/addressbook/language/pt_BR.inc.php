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
 * @version $Id: pt_BR.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Contatos';
$lang['addressbook']['description'] = 'Módulo para administrar todos os contatos.';



$lang['addressbook']['allAddressbooks'] = 'Todos os contatos';
$lang['common']['addressbookAlreadyExists'] = 'O contato que você está criando já existe';
$lang['addressbook']['notIncluded'] = 'Não importe';

$lang['addressbook']['comment'] = 'Comentário';
$lang['addressbook']['bankNo'] = 'Nº banco';
$lang['addressbook']['vatNo'] = 'Nº VAT';
$lang['addressbook']['contactsGroup'] = 'Grupo';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Contato';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Empresa';

$lang['addressbook']['customers'] = 'Clientes';
$lang['addressbook']['suppliers'] = 'Fornecedores';
$lang['addressbook']['prospects'] = 'Prospectos';


$lang['addressbook']['contacts'] = 'Contatos';
$lang['addressbook']['companies'] = 'Empresas';

$lang['addressbook']['newContactAdded']='Novo contato adicionado';
$lang['addressbook']['newContactFromSite']='Um novo contato foi adicionado através de um formulário web.';
$lang['addressbook']['clickHereToView']='Clique aqui para visulizar o contato';

$lang['addressbook']['contactFromAddressbook']='%s';
$lang['addressbook']['companyFromAddressbook']='%s';
$lang['addressbook']['defaultSalutation']='Prezado [Sr./Sra.] {first_name} {last_name}';

?>
