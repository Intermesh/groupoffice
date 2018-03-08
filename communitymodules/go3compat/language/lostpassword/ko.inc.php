<?php
//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('lostpassword'));

$lang['lostpassword']['success']='<h1>비밀번호가 바뀌었습니다</h1><p>비밀번호가 성공적으로 바뀌었습니다. 로그인 페이지로 진행할 수 있습니다.</p>';
$lang['lostpassword']['send']='보내기';
$lang['lostpassword']['login']='로그인';

$lang['lostpassword']['lost_password_subject']='새 비밀번호 요청';
$lang['lostpassword']['lost_password_body']='%s,

You requested a new password for %s. Your username is "%s".

Click at the link below (or paste it in a browser) to change your password:

%s

If you did not request a new password please delete this mail.';

$lang['lostpassword']['lost_password_error']='제공된 메일 주소를 찾을 수 없습니다';
$lang['lostpassword']['lost_password_success']='An e-mail with instructions has been sent to your e-mail address.';

$lang['lostpassword']['enter_password']='새 비밀번호를 입력하세요';

$lang['lostpassword']['new_password']='New password';
$lang['lostpassword']['lost_password']='Lost password';

$lang['lostpassword']['confirm_password']='Confirm password';
?>
