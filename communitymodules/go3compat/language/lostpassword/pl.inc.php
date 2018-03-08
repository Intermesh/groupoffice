<?php
//Polish Translation v1.1
//Author : Paweł Dmitruk pawel.dmitruk@gmail.com
//Date : September, 03 2010
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));
$lang['lostpassword']['success']='<h1>Zmiana hasła</h1><p>Twoje hasło zostało zmienione. Możesz przejść do srtony logowania.</p>';
$lang['lostpassword']['send']='Wyślij';
$lang['lostpassword']['login']='Login';
$lang['lostpassword']['lost_password_subject']='Prośba o nowe hasło';
$lang['lostpassword']['lost_password_body']='%s,

Wysłano prośbę o zmianę hasła z %s. Twoja nazwa użytkownika to "%s".

Kliknij w poniższy odnośnik (lub wklej do przeglądarki) aby zmienić hasło:

%s

Jeśli nie wysyłełeś prośby o zmianę hasła to skasuj tą wiadomość.';
$lang['lostpassword']['lost_password_error']='Nie można odnaleźć podanego adresu e-mail.';
$lang['lostpassword']['lost_password_success']='Instrukcję zmiany hasła wysłano na Twój adres e-mail.';
$lang['lostpassword']['enter_password']='Wprowadź nowe hasło';
$lang['lostpassword']['new_password']='Nowe hasło';
$lang['lostpassword']['lost_password']='Zapomniałem hasło';
$lang['lostpassword']['confirm_password']='Powtórz hasło';
?>