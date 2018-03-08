<?php
$this->render('externalHeader');
?>

<p>Your <?php echo \GO::config()->product_name; ?> trial installation has been created. Click at the URL below and login with the credentials you have been given in the e-mail.</p>

<p><a href="<?php echo $data['installation']->url; ?>"><?php echo $data['installation']->url; ?></a>


<?php
$this->render('externalFooter');