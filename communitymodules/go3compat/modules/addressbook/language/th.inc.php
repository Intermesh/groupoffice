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
 * @version $Id: th.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'สมุดที่อยู่';
$lang['addressbook']['description'] = 'ส่วนการจัดการผู้ติดต่อทั้งหมด.';



$lang['addressbook']['allAddressbooks'] = 'สมุดที่อยู่ทั้งหมด';
$lang['common']['addressbookAlreadyExists'] = 'คุณพยายามสร้างสมุดที่อยู่ที่มีอยู่เดิมแล้ว';//The addressbook you are trying to create already exists
$lang['addressbook']['notIncluded'] = 'ห้ามทำการนำเข้า';

$lang['addressbook']['comment'] = 'คำอธิบาย';
$lang['addressbook']['bankNo'] = 'หมายเลขบัญชี'; //Bank number
$lang['addressbook']['vatNo'] = 'หมายเลขผู้เสียภาษี';
$lang['addressbook']['contactsGroup'] = 'กลุ่ม';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'ผู้ติดต่อ';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'หน่วยงาน';

$lang['addressbook']['customers'] = 'ลูกค้า';
$lang['addressbook']['suppliers'] = 'ผู้จัดหา';
$lang['addressbook']['prospects'] = 'คุณสมบัติ';


$lang['addressbook']['contacts'] = 'ผู้ติดต่อ';
$lang['addressbook']['companies'] = 'หน่วยงาน';

$lang['addressbook']['newContactAdded']='เพิ่มผู้ติดต่อใหม่';
$lang['addressbook']['newContactFromSite']='ได้ทำการผู้ติดต่อใหมแล้ว.';
$lang['addressbook']['clickHereToView']='คลิกเพื่อดูรายการผู้ติดต่อ';
$lang['addressbook']['contactFromAddressbook']='ติดต่อจาก %s';
$lang['addressbook']['companyFromAddressbook']='จากหน่วยงาน %s';
$lang['addressbook']['defaultSalutation']='ถึง [Mr./Mrs.] {middle_name} {last_name}';
?>