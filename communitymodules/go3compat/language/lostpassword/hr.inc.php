<?php
	/** 
		* @copyright Copyright Boso d.o.o.
		* @author Mihovil Stanić <mihovil.stanic@boso.hr>
	*/
	
//Uncomment this line in new translations!
require($GO_LANGUAGE->get_fallback_base_language_file('lostpassword'));

$lang['lostpassword']['success']='<h1>Lozinka promjenjena</h1><p>Vaša lozinka je uspješno promjenjena. Možete nastaviti do stranice za prijavu.</p>';
$lang['lostpassword']['send']='Pošalji';
$lang['lostpassword']['login']='Prijavi se';

$lang['lostpassword']['lost_password_subject']='Zahtjev za novom lozinkom';
$lang['lostpassword']['lost_password_body']='%s,

Zatražili ste novu lozinku za %s. Vaše korisničko ime je "%s".

Kliknite na link ispod (ili ga kopirajte u vaš preglednik interneta) kako bi ste promjenili svoju lozinku:

%s

Ako niste zatražili novu lozinku molimo izbrišite ovaj e-mail.';

$lang['lostpassword']['lost_password_error']='Unesena e-mail adresa nije pronađena.';
$lang['lostpassword']['lost_password_success']='E-mail sa uputstvima je poslan na vašu e-mail adresu.';

$lang['lostpassword']['enter_password']='Molimo unesite novu lozinku';

$lang['lostpassword']['new_password']='Nova lozinka';
$lang['lostpassword']['lost_password']='Izgubljena lozinka';

$lang['lostpassword']['confirm_password']='Potvrdi lozinku';
?>
