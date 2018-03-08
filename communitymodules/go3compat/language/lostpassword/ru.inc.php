<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));

$lang['lostpassword']['success']='<h1>Пароль изменен</h1><p>Ваш пароль был изменен. Теперь вы можете войти в систему.</p>';
$lang['lostpassword']['send']='Отправить';
$lang['lostpassword']['login']='Имя пользователя';

$lang['lostpassword']['lost_password_subject']='Запрос нового пароля';
$lang['lostpassword']['lost_password_body']='%s,

Вы запросили новый пароль для %s. Имя пользователя "%s".

Для смены пароля нажмите на ссылку или скопируйте ее и вставьте в строку адреса вашего Инеренет браузера:

%s

Если Вы не запришивали новый пароль, удалите это письмо.';

$lang['lostpassword']['lost_password_error']='Указанный e-mail адрес не найден.';
$lang['lostpassword']['lost_password_success']='Письмо с инструкцией по замене пароля выслано на указанный e-mail.';

$lang['lostpassword']['enter_password']='Введите новый пароль';

$lang['lostpassword']['new_password']='Новый пароль';
$lang['lostpassword']['lost_password']='Забыли пароль';

$lang['lostpassword']['confirm_password']='Подтверждение пароля';
?>
