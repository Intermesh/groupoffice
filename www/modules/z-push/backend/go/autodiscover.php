<?php

/********************************
 * Autodiscover responder
 ********************************
 * This PHP script is intended to respond to any request to http(s)://mydomain.com/Autodiscover/Autodiscover.xml.
 * If configured properly, it will send a spec-complient autodiscover XML response, pointing mail clients to the
 * appropriate mail services.
 * If you use MAPI or ActiveSync, stick with the Autodiscover service your mail server provides for you. But if
 * you use POP/IMAP servers, this will provide autoconfiguration to Outlook, Apple Mail and mobile devices.
 *
 * To work properly, you'll need to set the service (sub)domains below in the settings section to the correct
 * domain names, adjust ports and SSL.
 */

//get raw POST data so we can extract the email address
$request = file_get_contents("php://input");

// We do not accept full email login
if(strpos($_SERVER['PHP_AUTH_USER'], '@') !== false) {
	http_response_code(401);
    exit();
}

function error($text) {
	file_put_contents( 'request.log', $_SERVER['PHP_AUTH_USER'].': ' . $text .  "\n", FILE_APPEND );
	http_response_code(500);
	exit();
}

const SCHEMA = 'http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006';
$asHost = 'https://' . ($_SERVER['HTTP_HOST'] ? : $_SERVER['SERVER_NAME']) . '/Microsoft-Server-ActiveSync';


file_put_contents('request.log', var_export(apache_request_headers(), true), FILE_APPEND);

// optional debug log
file_put_contents( 'request.log', $request . "\n\n", FILE_APPEND );

// retrieve email address from client request
preg_match( '/<EMailAddress>(.*?)<\/EMailAddress>/', $request, $email );

preg_match("/<AcceptableResponseSchema>(.*?)<\/AcceptableResponseSchema>/", $request, $schema);
//$schema = $schema[1];
if(!isset($schema[1])) {
	error('No Response schema provided');
}
if(!isset($email[1])) {
	error('No E-Mail provided');
}

file_put_contents( 'request', $email[1] .' '. $_SERVER['PHP_AUTH_USER'] . "\n\n", FILE_APPEND );

// check for invalid mail, to prevent XSS
if (filter_var($email[1], FILTER_VALIDATE_EMAIL) === false) {
	error('Invalid E-Mail provided');
}
if($schema[1] !== SCHEMA) {
	error('Invalid schema requested');
}

$response = '<?xml version="1.0" encoding="utf-8"?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
    <Response xmlns="'.SCHEMA.'">
        <Culture>en:us</Culture>
        <User>
            <DisplayName>'.$email[1].'</DisplayName>
            <EMailAddress>'.$email[1].'</EMailAddress>
        </User>
        <Action>
            <Settings>
                <Server>
                    <Type>MobileSync</Type>
                    <Url>' . $asHost . '</Url>
                    <Name>' . $asHost . '</Name>
                </Server>
            </Settings>
        </Action>
    </Response>
</Autodiscover>';

file_put_contents( 'request.log', $response ."\n\n", FILE_APPEND );

header( 'Content-Type: text/xml; charset=utf-8' );
echo $response;