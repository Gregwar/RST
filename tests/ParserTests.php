<?php

use Gregwar\RST\Parser;

use Gregwar\RST\Document;

use Gregwar\RST\Nodes\ParagraphNode;
use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Nodes\QuoteNode;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\ListNode;
use Gregwar\RST\Nodes\SeparatorNode;

/**
 * Unit testing for RST
 */
class FormTests extends \PHPUnit_Framework_TestCase
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
    public function testTitlse()
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
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse($file)
    {
        $parser = new Parser;

        return $parser->parse(file_get_contents(__DIR__.'/files/'.$file));
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
