<?php
//URL to Group-Office. You must use SSL because we use basic auth!
$groupoffice_url = "https://localhost/groupoffice-6.1/www/";

//Group-Office username and password. You should create a restricted user that 
//can only add contacts to this addressbook.
$username = 'website';
$password = 'secret';

//The contact properties to POST
$post = array(
		'addressbook_id' => 1, //required
		'company_id' => 0,
		'first_name' => 'Curl',
		'last_name' => 'Tester',
		'initials' => '',
		'title' => '',
		'suffix' => '',
		'sex' => 'M', // or 'F"
		'birthday' => '', //local format
 		'email' => '',
		'email2' => '',
		'email3' => '',
		'department' => '',
		'function' => '',
		'home_phone' => '',
		'work_phone' => '',
		'fax' => '',
		'work_fax' => '',
		'cellular' => '',
		'cellular2' => '',
		'homepage' => '',
		'country' => 'NL', //2 character ISO code 
		'state' => '',
		'city' => '',
		'zip' => '',
		'address' => '',
		'address_no' => '',
		'comment' => ''
);

$process = curl_init($groupoffice_url . '?r=addressbook/contact/submit');
curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($process, CURLOPT_POST, 1);
curl_setopt($process, CURLOPT_POSTFIELDS, $post);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

$return = curl_exec($process);
curl_close($process);

//JSON decode the response
$json = json_decode($return, true);


//Handle success or error here.
if ($json['success']) {
	echo "Contact saved!\n";
} else {
	echo "Failed to save contact: " . $json['feedback'] . "\n";
}