# Introduction
This is a Guzzle plugin to support Bearer Authentication as specified in RFC 
6750.

# Example Use

    <?php
    require_once 'vendor/autoload.php';

    use Guzzle\Http\Client;
    use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
    use fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException;
    use Guzzle\Http\Exception\BadResponseException;

    try {
        $client = new Client();

        $bearerAuth = new BearerAuth("12345");
        $client->addSubscriber($bearerAuth);
        $response = $client->get('http://api.example.org/resource')->send();
        echo $response->getBody();
    } catch (BearerErrorResponseException $e) {
        echo $e->getMessage() . PHP_EOL;
    } catch (BadResponseException $e) {
        echo $e->getMessage() . PHP_EOL;
    }

# Exceptions
The `BearerErrorResponseException` can be used to figure out what went wrong
with the Bearer authentication. The reason is available using 
`getBearerReason()`. The values are defined in RFC 6750 section 3.1, so either
`invalid_request`, `invalid_token` or `insufficient_scope`. You can use this 
to figure out what went wrong and e.g. remove the access token from your 
storage or mark it as unusable.