## Add this following line to your view:

<img id="captcha" src="<?php echo GO::url('/site/account/captcha');?>" alt="Secure registration" />


<input type="text" name="captcha_code" size="10" maxlength="6" />
<a  onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>


## Add the following line to your controller:

if (\GO\Site\Widget\Secureimage\Secure::instance()->check($_POST['captcha_code']) == false) {
	echo 'FAIL';
}