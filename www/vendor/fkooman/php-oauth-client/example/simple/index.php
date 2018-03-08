<?php

require_once 'vendor/autoload.php';

$apiUri = "http://localhost/oauth/php-oauth/api.php/authorizations/";

$clientConfig = new fkooman\OAuth\Client\ClientConfig(
    array(
        "authorize_endpoint" => "http://localhost/oauth/php-oauth/authorize.php",
        "client_id" => "php-oauth-client-example",
        "client_secret" => "f00b4r",
        "token_endpoint" => "http://localhost/oauth/php-oauth/token.php",
    )
);

$tokenStorage = new fkooman\OAuth\Client\SessionStorage();
$httpClient = new Guzzle\Http\Client();
$api = new fkooman\OAuth\Client\Api("foo", $clientConfig, $tokenStorage, $httpClient);

$context = new fkooman\OAuth\Client\Context("john.doe@example.org", array("authorizations"));

$accessToken = $api->getAccessToken($context);
if (false === $accessToken) {
    /* no valid access token available, go to authorization server */
    header("HTTP/1.1 302 Found");
    header("Location: " . $api->getAuthorizeUri($context));
    exit;
}

try {
    $client = new Guzzle\Http\Client();
    $bearerAuth = new fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
    $client->addSubscriber($bearerAuth);
    $response = $client->get($apiUri)->send();
    header("Content-Type: application/json");
    echo $response->getBody();
} catch (fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
    if ("invalid_token" === $e->getBearerReason()) {
        // the token we used was invalid, possibly revoked, we throw it away
        $api->deleteAccessToken($context);
        $api->deleteRefreshToken($context);
        /* no valid access token available, go to authorization server */
        header("HTTP/1.1 302 Found");
        header("Location: " . $api->getAuthorizeUri($context));
        exit;
    }
    throw $e;
} catch (Exception $e) {
    die(sprintf('ERROR: %s', $e->getMessage()));
}
