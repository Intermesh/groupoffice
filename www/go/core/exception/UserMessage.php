<?php
namespace go\core\exception;

/**
 * Generic exception with a message that is safe to show to the end user.
 *
 * Put technical details in the log (or the previous exception), not in the message.
 */
class UserMessage extends \Exception implements UserSafeException
{
}
