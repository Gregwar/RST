<?php

use Gregwar\RST\Nodes\Node;
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
use Gregwar\RST\Nodes\DummyNode;

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

    public function testDirective()
    {
        $document = $this->parse('directive.rst');

        $nodes = $document->getNodes(function($node) {
            return $node instanceof DummyNode;
        });

        $this->assertEquals(1, count($nodes));

        if ($nodes) {
            $node = $nodes[0];
            $data = $node->data;
            $this->assertEquals('some data', $data['data']);
            $options = $data['options'];
            $this->assertTrue(isset($options['maxdepth']));
            $this->assertTrue(isset($options['titlesonly']));
            $this->assertTrue(isset($options['glob']));
            $this->assertTrue($options['titlesonly']);
            $this->assertEquals(123, $options['maxdepth']);
        }
    }

    public function testSubsequentParsesDontHaveTheSameTitleLevelOrder()
    {
        $directory = __DIR__ . '/files';

        $parser = new Parser;
        $parser->getEnvironment()->setCurrentDirectory($directory);

        /** @var TitleNode[] $nodes1 */
        /** @var TitleNode[] $nodes2 */
        $nodes1 = $parser->parseFile("$directory/mixed-titles-1.rst")->getNodes();
        $nodes2 = $parser->parseFile("$directory/mixed-titles-2.rst")->getNodes();

        $this->assertSame(1, $nodes1[0]->getLevel());
        $this->assertSame(2, $nodes1[1]->getLevel());
        $this->assertSame(1, $nodes2[0]->getLevel(), 'Title level in second parse is influenced by first parse');
        $this->assertSame(2, $nodes2[1]->getLevel(), 'Title level in second parse is influenced by first parse');
    }

    public function testNewlineBeforeAnIncludedIsntGobbled()
    {
        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-newline.rst')->getNodes();

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf('Gregwar\RST\Nodes\TitleNode', $nodes[0]);
        $this->assertInstanceOf('Gregwar\RST\Nodes\ParagraphNode', $nodes[1]);
        $this->assertInstanceOf('Gregwar\RST\Nodes\ParagraphNode', $nodes[2]);
        $this->assertContains('<p>Test this paragraph is present.</p>', $nodes[1]->render());
        $this->assertContains('<p>And this one as well.</p>', $nodes[2]->render());
    }

    public function testIncludesKeepScope()
    {
        // See http://docutils.sourceforge.net/docs/ref/rst/directives.html#including-an-external-document-fragment

        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-scope.rst')->getNodes();

        $this->assertCount(4, $nodes);
        $this->assertEquals("This first example will be parsed at the document level, and can\nthus contain any construct, including section headers.", $nodes[0]->getValue()->render());
        $this->assertEquals('This is included.', $nodes[1]->getValue()->render());
        $this->assertEquals('Back in the main document.', $nodes[2]->getValue()->render());
        $this->assertInstanceOf('Gregwar\RST\Nodes\QuoteNode', $nodes[3]);
        $this->assertContains('This is included.', $nodes[3]->getValue()->render());
    }

    public function testIncludesPolicy()
    {
        $directory = __DIR__.'/files/';
        $parser = new Parser;
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        // Test defaults
        $this->assertTrue($parser->getIncludeAllowed());
        $this->assertSame('', $parser->getIncludeRoot());

        // Default policy:
        $document = (string) $parser->parseFile($directory.'inclusion-policy.rst');
        $this->assertContains('SUBDIRECTORY OK', $document);
        $this->assertContains('EXTERNAL FILE INCLUDED!', $document);

        // Disbaled policy:
        $parser->setIncludePolicy(false);
        $nodes = $parser->parseFile($directory.'inclusion-policy.rst')->getNodes();
        $this->assertCount(1, $nodes);

        // Enabled
        $parser->setIncludePolicy(true);
        $nodes = $parser->parseFile($directory.'inclusion-policy.rst')->getNodes();
        $this->assertCount(6, $nodes);

        // Jailed
        $parser->setIncludePolicy(true, $directory);
        $nodes = $parser->parseFile($directory.'inclusion-policy.rst')->getNodes();
        $this->assertCount(5, $nodes);
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

        $data = file_get_contents($directory.$file);
        return $parser->parse($data);
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
