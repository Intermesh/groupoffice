<?php

require('header.php');

if($_SERVER['REQUEST_METHOD']=='POST'){
	try{
		foreach($_POST as $key=>$value){
			\GO::config()->$key=$value;
		}
		\GO::config()->save();
		$conn = \GO::getDbConnection();
		
		redirect('install.php');
		
	}
	catch(Exception $e){
		$error = "Could not connect to the database. The database returned this error:<br />".$e->getMessage();
	}
}

printHead();
if(isset($error))
		errorMessage($error);
?>
<h1>Database connection</h1>
<p>
Create a database now and fill in the values to connect to your database.<br />
The database user should have permission to perform select-, insert-, update- and delete queries. It must also be able to lock tables.<br /><br />

If you are upgrading then now is the last time to back up your database! Fill in the fields and click at 'Continue' to upgrade your database structure.
</p>

<div class="cmd">
$ mysql -u root -p<br />
mysql&#62; CREATE DATABASE groupoffice;<br />
mysql&#62; GRANT ALL PRIVILEGES ON groupoffice.* TO 'groupoffice'@'localhost'<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&#62; IDENTIFIED BY 'some_pass' WITH GRANT OPTION;<br />
mysql&#62; quit;<br />
</div>

<?php

\GO\Base\Html\Input::render(array(
		"label"=>"Host",
		"name"=>"db_host",
		"value"=>\GO::config()->db_host
));


\GO\Base\Html\Input::render(array(
		"label"=>"Port",
		"name"=>"db_port",
		"value"=>\GO::config()->db_port
));

\GO\Base\Html\Input::render(array(
		"label"=>"Name",
		"name"=>"db_name",
		"value"=>\GO::config()->db_name
));

\GO\Base\Html\Input::render(array(
		"label"=>"Username",
		"name"=>"db_user",
		"value"=>\GO::config()->db_user
));

\GO\Base\Html\Password::render(array(
		"label"=>"Password",
		"name"=>"db_pass",
		"value"=>""
));


continueButton();

printFoot();