<?php

require('header.php');

if($_SERVER['REQUEST_METHOD']=='POST' && \GO\Base\Html\Error::checkRequired()){

	foreach($_POST as $key=>$value){
		\GO::config()->$key=$value;
	}
	\GO::config()->save();

	header('Location: database.php');
}

printHead();
if(isset($error))
		errorMessage($error);
?>
<h1>SMTP server</h1>
<p>
	<?php echo \GO::config()->product_name; ?> needs to connect to an SMTP server to send e-mail. Please fill in the details for your SMTP server.
</p>

<?php

\GO\Base\Html\Input::render(array(
		"label"=>"SMTP server",
		"name"=>"smtp_server",
		"value"=>\GO::config()->smtp_server,
		"required"=>true
));


\GO\Base\Html\Input::render(array(
		"label"=>"Port",
		"name"=>"smtp_port",
		"value"=>\GO::config()->smtp_port,
		"required"=>true
));

?>
<p>
If your SMTP server requires authentication please fill in the username and password.
</p>
<?php

\GO\Base\Html\Input::render(array(
		"label"=>"Username",
		"name"=>"smtp_username",
		"value"=>\GO::config()->smtp_username
));


\GO\Base\Html\Input::render(array(
		"label"=>"Password",
		"name"=>"smtp_password",
		"value"=>\GO::config()->smtp_password
));

\GO\Base\Html\Select::render(array(
		"label"=>"Encryption",
		"name"=>"smtp_encryption",
		"value"=>\GO::config()->smtp_encryption,
		"options"=>array(
				''=>'No encryption',
				'ssl'=>'SSL',
				'tls'=>'TLS')
));


continueButton();

printFoot();