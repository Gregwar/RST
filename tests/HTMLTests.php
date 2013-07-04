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
     * Testing the emphasis
     */
    public function testEmphasis()
    {
        $document = $this->parseHTML('italic.rst');

        $this->assertContains('<em>italic emphasis</em>', $document);

        $document = $this->parseHTML('strong.rst');

        $this->assertContains('<b>strong emphasis</b>', $document);
    }

    /**
     * Testing a table
     */
    public function testTable()
    {
        $document = $this->parseHTML('table.rst');

        $this->assertEquals(substr_count($document, '<table>'), 1);
        $this->assertEquals(substr_count($document, '</table>'), 1);
        $this->assertEquals(substr_count($document, '<tr>'), 2);
        $this->assertEquals(substr_count($document, '</tr>'), 2);
        $this->assertEquals(substr_count($document, '<td'), 6);
        $this->assertEquals(substr_count($document, '</td>'), 6);
        $this->assertNotContains('==', $document);
    }

    /**
     * Testing literals
     */
    public function testLiteral()
    {
        $document = $this->parseHTML('literal.rst');

        $code = 'this is a *boring* literal `a`_ containing some dirty things <3 hey_ !';
        $this->assertContains(htmlspecialchars($code), $document);
    }

    /**
     * Testing separators
     */
    public function testSeparator()
    {
        $document = $this->parseHTML('separator.rst');

        $this->assertContains('<hr />', $document);
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

        $this->assertEquals(substr_count($document, '<ul>'), 1);
        $this->assertEquals(substr_count($document, '</ul>'), 1);
        $this->assertNotContains('<ol>', $document);
        $this->assertEquals(substr_count($document, '<li>'), 4);
        $this->assertEquals(substr_count($document, '</li>'), 4);
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        $this->assertEquals(substr_count($document, '<ol>'), 1);
        $this->assertEquals(substr_count($document, '</ol>'), 1);
        $this->assertNotContains('<ul>', $document);
        $this->assertEquals(substr_count($document, '<li>'), 3);
        $this->assertEquals(substr_count($document, '</li>'), 3);
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);
    }

    /**
     * Testing css stylesheet
     */
    public function testStylesheet()
    {
        $document = $this->parseHTML('css.rst');

        $this->assertContains('<link rel="stylesheet" type="text/css" href="style.css"', $document);
    }

    /**
     * Testing quote
     */
    public function testQuote()
    {
        $document = $this->parseHTML('quote.rst');

        $this->assertEquals(substr_count($document, '<blockquote>'), 1);
        $this->assertContains('<br />', $document);
        $this->assertEquals(substr_count($document, '</blockquote>'), 1);
    }

    /**
     * Testing code blocks
     */
    public function testCode()
    {
        $document = $this->parseHTML('code.rst');

        $this->assertEquals(substr_count($document, '<pre>'), 1);
        $this->assertEquals(substr_count($document, '</pre>'), 1);
        $this->assertEquals(substr_count($document, '<code>'), 1);
        $this->assertEquals(substr_count($document, '</code>'), 1);
        $this->assertContains('This is a code block', $document);
        $this->assertNotContains('::', $document);
        $this->assertNotContains('<br', $document);

        $document = $this->parseHTML('code-block.rst');

        $this->assertEquals(substr_count($document, '<pre>'), 1);
        $this->assertEquals(substr_count($document, '</pre>'), 1);
        $this->assertEquals(substr_count($document, '<code>'), 1);
        $this->assertEquals(substr_count($document, '</code>'), 1);
        $code = 'cout << "Hello world!" << endl;';
        $this->assertContains(htmlspecialchars($code), $document);
    }

    /**
     * Testing titles
     */
    public function testTitles()
    {
        $document = $this->parseHTML('titles.rst');

        $this->assertEquals(substr_count($document, '<h1>'), 1);
        $this->assertEquals(substr_count($document, '<h1>'), 1);
        $this->assertEquals(substr_count($document, '<h2>'), 2);
        $this->assertEquals(substr_count($document, '</h2>'), 2);
        $this->assertEquals(substr_count($document, '<h3>'), 4);
        $this->assertEquals(substr_count($document, '</h3>'), 4);
        $this->assertContains('<a id="title', $document);
        $this->assertNotContains('==', $document);
        $this->assertNotContains('--', $document);
        $this->assertNotContains('~~', $document);
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
        return $this->parse($file)->renderDocument();
    }
}
