<?php

use Gregwar\RST\Parser;

use Gregwar\RST\Document;

use Gregwar\RST\Nodes\ParagraphNode;
use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Nodes\QuoteNode;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\ListNode;
use Gregwar\RST\Nodes\TableNode;
use Gregwar\RST\Nodes\SeparatorNode;

/**
 * Unit testing for RST
 */
class ParserTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that comments are not present in the rendered document
     */
    public function testComments()
    {
        $document = $this->parse('comment.rst');

        $this->assertNotContains('Testing comment', $document->render());
    }

    /**
     * Testing paragraph nodes
     */
    public function testParagraphNode()
    {
        $document = $this->parse('paragraph.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof ParagraphNode;
        }, 1);
        $this->assertContains('Hello world!', $document->render());
    }

    /**
     * Testing multi-paragraph nodes
     */
    public function testParagraphNodes()
    {
        $document = $this->parse('paragraphs.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof ParagraphNode;
        }, 3);
    }

    /**
     * Testing quote and block code
     */
    public function testBlockNode()
    {
        $quote = $this->parse('quote.rst');

        $this->assertHasNode($quote, function($node) {
            return $node instanceof QuoteNode;
        }, 1);
        
        $code = $this->parse('code.rst');

        $this->assertHasNode($quote, function($node) {
            return $node instanceof QuoteNode;
        }, 1);

        $this->assertNotContains('::', $code->render());
    }

    /**
     * Testing the titling
     */
    public function testTitles()
    {
        $document = $this->parse('title.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof TitleNode
                && $node->getLevel() == 1;
        }, 1);

        $document = $this->parse('title2.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof TitleNode
                && $node->getLevel() == 2;
        }, 1);
    }

    /**
     * Testing the titling
     */
    public function testList()
    {
        $document = $this->parse('list.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof ListNode;
        }, 1);
    }

    /**
     * Testing the titles retrieving
     */
    public function testGetTitles()
    {
        $document = $this->parse('titles.rst');

        $this->assertEquals($document->getTitle(), 'The main title');
        $this->assertEquals($document->getTitles(), array(
            array('The main title', array(
                array('First level title', array(
                    array('Second level title', array()),
                    array('Other second level title', array())
                )),
                array('Other first level title', array(
                    array('Next second level title', array()),
                    array('Yet another second level title', array())
                ))
            )))
        );
    }

    /**
     * Testing the table feature
     */
    public function testTable()
    {
        $document = $this->parse('table.rst');

        $nodes = $document->getNodes(function($node) {
            return $node instanceof TableNode;
        });

        $this->assertEquals(count($nodes), 1);

        if ($nodes) {
            $table = $nodes[0];
            $this->assertEquals($table->getCols(), 3);
            $this->assertEquals($table->getRows(), 3);
        }
    }

    /**
     * Test the include:: pseudo-directive
     */
    public function testInclusion()
    {
        $document = $this->parse('inclusion.rst');

        $this->assertContains('I was actually included', $document->renderDocument());
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse($file)
    {
        $directory = __DIR__.'/files/';
        $parser = new Parser;
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        return $parser->parse(file_get_contents($directory.$file));
    }

    /**
     * Asserts that a document has nodes that satisfy the function
     */
    private function assertHasNode(Document $document, $function, $count = null)
    {
        $nodes = $document->getNodes($function);
        $this->assertNotEmpty($nodes);

        if ($count !== null) {
            $this->assertEquals(count($nodes), $count);
        }
    }

    /**
     * Asserts that a document has nodes that satisfy the function
     */
    private function assertNotHasNode(Document $document, $function)
    {
        $nodes = $document->getNodes($function);
        $this->assertEmpty($nodes);
    }
}
