<?php

use Gregwar\RST\HTML\Span;
use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Exception\InvalidArgumentException;
use Gregwar\RST\Tests\Roles\PhpNetReference;
use Gregwar\RST\Tests\Roles\TestHtmlKernel;
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

    public function testCustomReferencesStillWork()
    {
        $kernel = new TestHtmlKernel();
        $kernel->references[] = new PhpNetReference();

        $parser = new Parser(null, $kernel);

        $html = $parser->parse(':method:`strlen`')->render();
        $this->assertEquals("<p><a href=\"http://php.net/strlen\">strlen</a></p>\n", $html);
    }

    public function testRoleDomainsAreSupported()
    {
        $parser = $this->getMockBuilder('Gregwar\RST\Parser')->getMock();

        $environment = $this->getMockBuilder('Gregwar\RST\Environment')->getMock();
        $environment->method('getTitleLetters')->willReturn(array());
        $environment->expects($this->once())->method('processRole')->with('ns:role', 'contents', $parser);

        $parser->method('getEnvironment')->willReturn($environment);

        new Span($parser, ':ns:role:`contents`');
    }
}
