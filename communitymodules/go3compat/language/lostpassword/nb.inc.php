<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));

$lang['lostpassword']['success']='<h1>Passordet er endret</h1><p>Du har endret passordet ditt, og kan nå logge inn med ditt nye passord.</p>';
$lang['lostpassword']['send']='Send';
$lang['lostpassword']['login']='Logg inn';

$lang['lostpassword']['lost_password_subject']='Forespørsel om nytt passord';
$lang['lostpassword']['lost_password_body']='%s,

Du har bedt om et nytt passord for %s. Brukernavnet ditt er "%s".

Trykk på lenken nedenfor eller lim den inn i nettleseren for å endre passordt ditt:

%s

Hvis du ikke har bedt om et nytt passord kan du bare slette denne e-posten.';

$lang['lostpassword']['lost_password_error']='Fant ingen konto med denne e-postadressen.';
$lang['lostpassword']['lost_password_success']='En e-post med videre instrukser er sendt til din e-postadresse.';

$lang['lostpassword']['enter_password']='Oppgi nytt passord';

$lang['lostpassword']['new_password']='Nytt passord';
$lang['lostpassword']['lost_password']='Mistet passord';

$lang['lostpassword']['confirm_password']='Bekreft passord';
?>
