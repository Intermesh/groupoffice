<?php

namespace fkooman\Guzzle\Plugin\BearerAuth;

use Guzzle\Common\Event;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException;

class BearerAuth implements EventSubscriberInterface
{
    private $bearerToken;

    public function __construct($bearerToken)
    {
        $this->bearerToken = $bearerToken;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => 'onRequestBeforeSend',
            'request.exception' => 'onRequestException'
        );
    }

    public function onRequestBeforeSend(Event $event)
    {
        if (!is_null($event) && !is_null($event['request'])) {
            $event['request']->setHeader("Authorization", sprintf("Bearer %s", $this->bearerToken));
        }
    }

    public function onRequestException(Event $event)
    {
        if (!is_null($event)
            && !is_null($event['response'])
            && !is_null($event['response']->getHeader("WWW-Authenticate"))) {
            throw BearerErrorResponseException::factory($event['request'], $event['response']);
        }
    }
}
