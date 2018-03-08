<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));

$lang['lostpassword']['success']='<h1>Lösenord bytt</h1><p>Ditt lösenord är bytt. Du kan nu fortsätta till login-sidan.</p>';
$lang['lostpassword']['send']='Skicka';
$lang['lostpassword']['login']='Logga in';

$lang['lostpassword']['lost_password_subject']='Begäran om nytt lösenord';
$lang['lostpassword']['lost_password_body']='%s,

Du begärde ett nytt lösenord för %s. Ditt användarnamn är "%s".

Klicka på länken nedan (eller klistra in den i en webbläsare) för att ändra ditt lösenord:

%s

Radera det här meddelandet om du inte begärt ett nytt lösenord.';

$lang['lostpassword']['lost_password_error']='Kunde inte hitta den angivna e-postadressen.';
$lang['lostpassword']['lost_password_success']='Ett meddelande med instruktioner har skickats till din e-post.';

$lang['lostpassword']['enter_password']='Ange ett nytt lösenord';

$lang['lostpassword']['new_password']='Nytt lösenord';
$lang['lostpassword']['lost_password']='Förlorat lösenord';

$lang['lostpassword']['confirm_password']='Bekräfta lösenord';
?>
