<?php

use Gregwar\RST\Parser;
use Gregwar\RST\Document;

use Gregwar\RST\Builder;

/**
 * Unit testing for RST
 */
class BuilderTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that the build produced the excepted documents
     */
    public function testBuild()
    {
        $this->assertTrue(is_dir($this->targetFile()));
        $this->assertTrue(file_exists($this->targetFile('index.html')));
        $this->assertTrue(file_exists($this->targetFile('introduction.html')));
        $this->assertTrue(file_exists($this->targetFile('file.txt')));
    }

    /**
     * Tests that the index toctree worked
     */
    public function testToctree()
    {
        $contents = file_get_contents($this->targetFile('index.html'));

        $this->assertContains('introduction.html', $contents);
        $this->assertContains('Introduction page', $contents);
    }

    public function setUp()
    {
        shell_exec('rm -rf '.$this->targetFile());
        $builder = new Builder;
        $builder->copy('file.txt');
        $builder->build($this->sourceFile(), $this->targetFile(), false);
    }

    protected function sourceFile($file = '')
    {
        return __DIR__.'/builder/input/'.$file;
    }

    protected function targetFile($file = '')
    {
        return __DIR__.'/builder/output/'.$file;
    }
}
