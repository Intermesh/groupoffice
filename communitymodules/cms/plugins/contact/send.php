<?php
require('../../Group-Office.php');


$name = (trim($_POST['name']));
$email = (trim($_POST['email']));
$subject = (trim($_POST['subject']));  

$subject = str_replace("\r",'',$subject);			
$subjectArr = explode("\n", $subject);


$message = (trim($_POST['message']));

if(stristr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false)
{
	//echo $_SERVER['HTTP_REFERER'].' -> '.$_SERVER['HTTP_HOST'];
	die('Referrer check failed');
}elseif(count($subjectArr)>1)
{
	//something posted a multi line subject
	//lets just die
	die();
}elseif ($name == '' || $email == '' || $subject == '' || $message == '')
{
	$feedback = 'missing_field';
}elseif(!String::validate_email($email))
{
	$feedback = 'invalid_email';
}else
{
	$email_to = !empty($this->attributes['email_to'])  ? $this->attributes['email_to'] : $this->cms_site->site['webmaster'];
	if(!sendmail($email_to, $email, $name, $subject, $message))
	{
		$feedback = $cms_sendmail_error;
	}else
	{
		return $GLOBALS['cms_sendmail_success'];									
	}    
}