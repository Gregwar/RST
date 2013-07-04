<?php

use Gregwar\RST\Parser;
use Gregwar\RST\Document;

/**
 * Unit testing for RST
 */
class HTMLTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Test some links demo
     */
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
     * Testing the images feature
     */
    public function testImage()
    {
        $document = $this->parseHTMl('image.rst');

        $this->assertContains('<img', $document);
        $this->assertContains('src="test.jpg"', $document);
        $this->assertContains('src="try.jpg"', $document);
        $this->assertContains('src="other.jpg"', $document);
        $this->assertContains('width="123"', $document);
        $this->assertContains('title="Other"', $document);
        $this->assertNotContains('..', $document);
        $this->assertNotContains('image', $document);
        $this->assertNotContains('::', $document);
    }

    /**
     * Testing a list
     */
    public function testList()
    {
        $document = $this->parseHTML('list.rst');

        $this->assertContains('<ul>', $document);
        $this->assertNotContains('<ol>', $document);
        $this->assertContains('<li>', $document);
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        $this->assertContains('<ol>', $document);
        $this->assertNotContains('<ul>', $document);
        $this->assertContains('<li>', $document);
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);
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
