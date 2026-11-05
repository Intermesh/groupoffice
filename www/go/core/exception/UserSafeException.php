<?php
namespace go\core\exception;

/**
 * Marker interface for exceptions whose message is written for end users.
 *
 * When debug mode is off, only the messages of exceptions implementing this
 * interface are sent to the client. All other exceptions are reduced to a
 * generic message with a reference ID. The message must never contain
 * sensitive information such as file paths, SQL, host names or credentials.
 *
 * @see \go\core\ErrorHandler::clientMessage()
 */
interface UserSafeException extends \Throwable
{
}
