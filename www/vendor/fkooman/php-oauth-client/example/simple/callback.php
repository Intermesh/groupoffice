<?php

require_once 'vendor/autoload.php';

$clientConfig = new fkooman\OAuth\Client\ClientConfig(
    array(
        "authorize_endpoint" => "http://localhost/oauth/php-oauth/authorize.php",
        "client_id" => "php-oauth-client-example",
        "client_secret" => "f00b4r",
        "token_endpoint" => "http://localhost/oauth/php-oauth/token.php",
    )
);

try {
    $tokenStorage = new fkooman\OAuth\Client\SessionStorage();
    $httpClient = new Guzzle\Http\Client();
    $cb = new fkooman\OAuth\Client\Callback("foo", $clientConfig, $tokenStorage, $httpClient);
    $cb->handleCallback($_GET);

    header("HTTP/1.1 302 Found");
    header("Location: http://localhost/php-oauth-client-example/index.php");
    exit;
} catch (fkooman\OAuth\Client\Exception\AuthorizeException $e) {
    // this exception is thrown by Callback when the OAuth server returns a
    // specific error message for the client, e.g.: the user did not authorize
    // the request
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (Exception $e) {
    // other error, these should never occur in the normal flow
    die(sprintf("ERROR: %s", $e->getMessage()));
}
