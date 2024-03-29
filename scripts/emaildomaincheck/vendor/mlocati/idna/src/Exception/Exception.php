<?php

namespace MLocati\IDNA\Exception;

/**
 * Base class for all the exception thrown by MLocati\IDNA.
 */
abstract class Exception extends \Exception
{
    /**
     * Get a simple string representation of a variable.
     *
     * @param mixed $var
     *
     * @return string
     */
    protected static function stringifyVariable($var)
    {
        $result = '';
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'integer':
            case 'double':
                return (string) $var;
            case 'string':
                return $var;
            case 'object':
                if (is_callable(array($var, '__toString'))) {
                    $result = (string) $var;
                }
                break;
        }

        return $result;
    }
}
