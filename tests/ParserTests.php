<?php

use Gregwar\RST\Parser;

/**
 * Unit testing for RST
 */
class FormTests extends \PHPUnit_Framework_TestCase
{
    public function testComments()
    {
        $document = $this->parse('comment.rst');

        $this->assertNotContains('Testing comment', $document->render());
    }

    private function parse($file)
    {
        $parser = new Parser;

        return $parser->parse(file_get_contents(__DIR__.'/files/'.$file));
    }
}
