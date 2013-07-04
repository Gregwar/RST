<?php

use Gregwar\RST\Parser;
use Gregwar\RST\Document;

/**
 * Unit testing for RST
 */
class HTMLTests extends \PHPUnit_Framework_TestCase
{
    public function testLinks()
    {
        $document = $this->parseHTML('links.rst');

        $this->assertContains('<a href="http://www.google.com/">', $document);
        $this->assertContains('<a href="http://xkcd.com/">', $document);
        $this->assertContains('<a href="http://something.com/">', $document);
        $this->assertContains('<a href="http://anonymous.com/">', $document);
        $this->assertContains('under_score', $document);
        $this->assertNotContains('`', $document);
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse($file)
    {
        $directory = __DIR__.'/html/';
        $parser = new Parser;
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        return $parser->parse(file_get_contents($directory.$file));
    }

    private function parseHTML($file)
    {
        return $this->parse($file)->render();
    }
}
