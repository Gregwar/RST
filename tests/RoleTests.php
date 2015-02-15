<?php

use Gregwar\RST\Roles\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;

class RoleTests extends TestCase
{
    public function testInvalidArgumentExceptionThrowsOnInvalidInternalType()
    {
        $this->setExpectedException(
            'Gregwar\RST\Roles\Exception\InvalidArgumentException',
            "Expected 'var' to be an object, got 'boolean'"
        );

        InvalidArgumentException::assert('var', true, 'Gregwar\RST\Roles\Reference');
    }

    public function testInvalidArgumentExceptionThrowsOnInvalidType()
    {
        $this->setExpectedException(
            'Gregwar\RST\Roles\Exception\InvalidArgumentException',
            "Expected 'var' to be of type 'Gregwar\RST\Roles\Reference', got 'stdClass'"
        );

        InvalidArgumentException::assert('var', new \stdClass, 'Gregwar\RST\Roles\Reference');
    }
}
