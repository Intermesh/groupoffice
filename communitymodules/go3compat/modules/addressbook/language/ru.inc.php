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
 * @version $Id: ru.inc.php 20766 2017-01-05 13:32:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
/**
 * Russian translation
 * By Valery Yanchenko (utf-8 encoding)
 * vajanchenko@hotmail.com
 * 10 December 2008
*/

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('addressbook'));
$lang['addressbook']['name'] = 'Контакты';
$lang['addressbook']['description'] = 'Модуль для управления всеми контактами.';



$lang['addressbook']['allAddressbooks'] = 'Все адресные книги';
$lang['common']['addressbookAlreadyExists'] = 'Адресная книга, которую Вы хотели создать уже существует';
$lang['addressbook']['notIncluded'] = 'Не загружается';

$lang['addressbook']['comment'] = 'Коментарий';
$lang['addressbook']['bankNo'] = 'Банковские реквизиты'; 
$lang['addressbook']['vatNo'] = 'Банковские реквизиты2';
$lang['addressbook']['contactsGroup'] = 'Группа';

$lang['link_type'][2]=$lang['addressbook']['contact'] = 'Контакт';
$lang['link_type'][3]=$lang['addressbook']['company'] = 'Компания';

$lang['addressbook']['customers'] = 'Клиенты';
$lang['addressbook']['suppliers'] = 'Поставщики';
$lang['addressbook']['prospects'] = 'Перспективные';


$lang['addressbook']['contacts'] = 'Контакты';
$lang['addressbook']['companies'] = 'Компании';

$lang['addressbook']['newContactAdded']='Добавлен новый контакт';
$lang['addressbook']['newContactFromSite']='Добавлен новый контакт через WEB-форму.';
$lang['addressbook']['clickHereToView']='Нажмите здесь для просмотра контакта';

$lang['addressbook']['contactFromAddressbook']='Контакт из %s';
$lang['addressbook']['companyFromAddressbook']='Компания из %s';
$lang['addressbook']['defaultSalutation']='Уважаемый(ая) {middle_name} {last_name}';

$lang['addressbook']['multipleSelected']='Выбрано несколько адресных книг';
$lang['addressbook']['incomplete_delete_contacts']='У Вас не прав на удаление всех выбранных контактов';
$lang['addressbook']['incomplete_delete_companies']='У Вас не прав на удаление всех выбранных компаний';

$lang['addressbook']['emailAlreadyExists']='E-mail адрес уже добавлен к этому контакту';
$lang['addressbook']['emailDoesntExists']='E-mail адрес не найден';

$lang['addressbook']['imageNotSupported']='Рисунок который Вы загрузили не поддерживается системой. Поддерживаются только gif, png и jpg рисунки.';
?>
