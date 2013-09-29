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

        $render = $document->render();
        $this->assertNotContains('Testing comment', $render);
        $this->assertContains('Text before', $render);
        $this->assertContains('Text after', $render);
        
        $document = $this->parse('multi-comment.rst');

        $render = $document->render();
        $this->assertNotContains('multi-line', $render);
        $this->assertNotContains('Blha', $render);
        $this->assertContains('Text before', $render);
        $this->assertContains('Text after', $render);
    }

    /**
     * Testing raw node
     */
    public function testRawNode()
    {
        $document = $this->parse('empty.rst');
        $document->addNode('hello');

        $this->assertContains('hello', $document->render());
    }

    /**
     * Testing that code node value is good
     */
    public function testCodeNode()
    {
        $document = $this->parse('code-block-lastline.rst');

        $nodes = $document->getNodes(function ($node) {
            return $node instanceof CodeNode;
        });

        $this->assertEquals(1, count($nodes));
        $this->assertEquals("A\nB\n C", trim($nodes[0]->getValue()));
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
        
        $document = $this->parse('indented-list.rst');
        
        $this->assertHasNode($document, function($node) {
            return $node instanceof ListNode;
        }, 1);

        $document = $this->parse('list-empty.rst');
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
            $this->assertEquals(3, $table->getCols());
            $this->assertEquals(3, $table->getRows());
        }
        
        $document = $this->parse('pretty-table.rst');

        $nodes = $document->getNodes(function($node) {
            return $node instanceof TableNode;
        });

        $this->assertEquals(count($nodes), 1);

        if ($nodes) {
            $table = $nodes[0];
            $this->assertEquals(3, $table->getCols(), 3);
            $this->assertEquals(2, $table->getRows(), 2);
        }
    }

    /**
     * Tests that a simple replace works
     */
    public function testReplace()
    {
        $document = $this->parse('replace.rst');

        $this->assertContains('Hello world!', $document->render());
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
            $this->assertEquals($count, count($nodes));
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
