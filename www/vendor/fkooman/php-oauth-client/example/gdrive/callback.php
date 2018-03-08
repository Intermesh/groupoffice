<?php

use fkooman\OAuth\Client\GoogleClientConfig;
use fkooman\OAuth\Client\Callback;
use fkooman\OAuth\Client\SessionStorage;
use fkooman\OAuth\Client\PdoStorage;

use Guzzle\Http\Client;

require_once 'vendor/autoload.php';

/* OAuth client configuration */
$clientConfig = new GoogleClientConfig(json_decode(file_get_contents("client_secrets.json"), true));

try {
    //$db = new PDO(sprintf("sqlite:%s/data/client.sqlite", __DIR__));
    //$tokenStorage = new PdoStorage($db);
    $tokenStorage = new SessionStorage();

    /* initialize the Callback */
    $cb = new Callback("php-drive-client", $clientConfig, $tokenStorage, new Client());
    /* handle the callback */
    $cb->handleCallback($_GET);

    header("HTTP/1.1 302 Found");
    header("Location: https://fkooman.pagekite.me/php-drive-client/index.php");

} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
}
