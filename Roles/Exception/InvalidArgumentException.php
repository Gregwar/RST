<?php

namespace Gregwar\RST\Roles\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    public static function assert($name, $value, $instanceOf)
    {
        if (!is_object($value)) {
            $type = gettype($value);

            throw new self(sprintf("Expected '%s' to be an object, got '%s'", $name, $type));
        }

        if (!$value instanceof $instanceOf) {
            $type = get_class($value);

            throw new self(sprintf("Expected '%s' to be of type '%s', got '%s'", $name, $type));
        }
    }
}
