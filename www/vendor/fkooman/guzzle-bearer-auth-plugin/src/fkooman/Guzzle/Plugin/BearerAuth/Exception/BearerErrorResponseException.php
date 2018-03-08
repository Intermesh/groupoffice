<?php

namespace fkooman\Guzzle\Plugin\BearerAuth\Exception;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\ClientErrorResponseException;

class BearerErrorResponseException extends ClientErrorResponseException
{
    private $bearerReason;

    public function getBearerReason()
    {
        return $this->bearerReason;
    }

    public function setBearerReason($bearerReason)
    {
        $this->bearerReason = $bearerReason;
    }

    public static function factory(RequestInterface $request, Response $response)
    {
        $label = 'Bearer error response';
        $bearerReason = self::headerToReason($response->getHeader("WWW-Authenticate"));
        $message = $label . PHP_EOL . implode(PHP_EOL,
            array(
                '[status code] ' . $response->getStatusCode(),
                '[reason phrase] ' . $response->getReasonPhrase(),
                '[bearer reason] ' . $bearerReason,
                '[url] ' . $request->getUrl(),
            )
        );

        $e = new static($message);
        $e->setResponse($response);
        $e->setRequest($request);
        $e->setBearerReason($bearerReason);

        return $e;
    }

    public static function headerToReason($header)
    {
        if (null !== $header) {
            $p = $header->parseParams();
            foreach ($p as $v) {
                if (isset($v['error'])) {
                    return $v['error'];
                }
            }
        }

        return null;
    }
}
