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
        $this->assertContains(' spacy', $document);
        $this->assertNotContains(' ,', $document);
        $this->assertNotContains('`', $document);
    }

    /**
     * Testing the non breakable spaces (~)
     */
    public function testNbsp()
    {
        $document = $this->parseHTML('nbsp.rst');

        $this->assertContains('&nbsp;', $document);
        $this->assertNotContains('~', $document);
    }

    /**
     * Testing that the text is ecaped
     */
    public function testEscape()
    {
        $document = $this->parseHTML('escape.rst');

        $this->assertContains('&lt;script&gt;', $document);
        $this->assertNotContains('<script>', $document);
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

        $this->assertEquals(1, substr_count($document, '<table>'));
        $this->assertEquals(1, substr_count($document, '</table>'));
        $this->assertEquals(2, substr_count($document, '<tr>'));
        $this->assertEquals(2, substr_count($document, '</tr>'));
        $this->assertEquals(6, substr_count($document, '<td'));
        $this->assertEquals(6, substr_count($document, '</td>'));
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

        $document = $this->parseHTML('image-inline.rst');

        $this->assertContains('<img', $document);
        $this->assertContains('src="test.jpg"', $document);
    }

    /**
     * Testing a list
     */
    public function testList()
    {
        $document = $this->parseHTML('list.rst');

        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertNotContains('<ol>', $document);
        $this->assertEquals(4, substr_count($document, '<li>'));
        $this->assertEquals(4, substr_count($document, '</li>'));
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);
        $this->assertContains('Last line', $document);
        
        $document = $this->parseHTML('indented-list.rst');

        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertNotContains('<ol>', $document);
        $this->assertEquals(4, substr_count($document, '<li>'));
        $this->assertEquals(4, substr_count($document, '</li>'));
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertNotContains('<ul>', $document);
        $this->assertEquals(3, substr_count($document, '<li>'));
        $this->assertEquals(3, substr_count($document, '</li>'));
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);

        $document = $this->parseHTML('ordered2.rst');

        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertNotContains('<ul>', $document);
        $this->assertEquals(3, substr_count($document, '<li>'));
        $this->assertEquals(3, substr_count($document, '</li>'));
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);

        $document = $this->parseHTML('list-empty.rst');
        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertEquals(5, substr_count($document, '<li>'));
        $this->assertEquals(5, substr_count($document, '</li>'));
        $this->assertContains('<p>This is not in the list</p>', $document);
    }

    public function testEmptyParagraph()
    {
        $document = $this->parseHTML('empty-p.rst');

        $this->assertNotContains('<p></p>', $document);
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

        $this->assertEquals(1, substr_count($document, '<blockquote>'));
        $this->assertContains('<br />', $document);
        $this->assertEquals(1, substr_count($document, '</blockquote>'));
    }

    /**
     * Testing code blocks
     */
    public function testCode()
    {
        $document = $this->parseHTML('code.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertEquals(1, substr_count($document, '<code>'));
        $this->assertEquals(1, substr_count($document, '</code>'));
        $this->assertContains('This is a code block', $document);
        $this->assertNotContains('::', $document);
        $this->assertNotContains('<br', $document);

        $document = $this->parseHTML('code-block.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertEquals(1, substr_count($document, '<code>'));
        $this->assertEquals(1, substr_count($document, '</code>'));
        $code = 'cout << "Hello world!" << endl;';
        $this->assertContains(htmlspecialchars($code), $document);
    }

    /**
     * Testing titles
     */
    public function testTitles()
    {
        $document = $this->parseHTML('titles.rst');

        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(2, substr_count($document, '<h2>'));
        $this->assertEquals(2, substr_count($document, '</h2>'));
        $this->assertEquals(4, substr_count($document, '<h3>'));
        $this->assertEquals(4, substr_count($document, '</h3>'));
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
