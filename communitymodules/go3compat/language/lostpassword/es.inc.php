<?php
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));
$lang['lostpassword']['success']='<h1>Contraseña modificada</h1><p>Su contraseña fue modificada exitosamente. Puede continuar a la página de logueo.</p>';
$lang['lostpassword']['send']='Enviar';
$lang['lostpassword']['login']='Login';
$lang['lostpassword']['lost_password_subject']='Pedido de nueva contraseña';
$lang['lostpassword']['lost_password_body']='%s,

Ud ha pedido una nueva contraseña para %s. Su usuario es "%s".

Haga click en el siguiente link (o peguelo en su navegador) para cambiar su contraseña:

%s

Si no ha pedido una nueva contraseña por favor borre este email.';
$lang['lostpassword']['lost_password_error']='No se pudo encontrar la dirección de e-mail.';
$lang['lostpassword']['lost_password_success']='Se le ha enviado un e-mail con las instrucciones.';
$lang['lostpassword']['enter_password']='Por favor ingrese una nueva contraseña';
$lang['lostpassword']['new_password']='Nueva contraseña';
$lang['lostpassword']['lost_password']='Contraseña perdida';
$lang['lostpassword']['confirm_password']='Confirme contraseña';
?>
