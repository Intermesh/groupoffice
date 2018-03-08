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
 * @version $Id: cn.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//This is a translation by hodrag. If you have questions please e-mail to hodrag@gmail.com
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = '通讯录';
$lang['addressbook']['description'] = '联系人管理模块.';



$lang['addressbook']['allAddressbooks'] = '所有通讯录';
$lang['common']['addressbookAlreadyExists'] = '你要创建的通讯录已经存在';
$lang['addressbook']['notIncluded'] = '不能导入';

$lang['addressbook']['comment'] = '备注';
$lang['addressbook']['bankNo'] = '银行帐号'; 
$lang['addressbook']['vatNo'] = 'VAT number';
$lang['addressbook']['contactsGroup'] = '组';

$lang['link_type'][2]=$lang['addressbook']['contact'] = '联系人';
$lang['link_type'][3]=$lang['addressbook']['company'] = '公司';

$lang['addressbook']['customers'] = '客户';
$lang['addressbook']['suppliers'] = '供应商';
$lang['addressbook']['prospects'] = '潜在客户';


$lang['addressbook']['contacts'] = '联系人';
$lang['addressbook']['companies'] = '公司';

?>
