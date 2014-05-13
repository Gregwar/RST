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
        $this->assertTrue(file_exists($this->targetFile('subdirective.html')));
        $this->assertTrue(file_exists($this->targetFile('magic-link.html')));
        $this->assertTrue(file_exists($this->targetFile('file.txt')));
        $this->assertTrue(file_exists($this->targetFile('subdir/test.html')));
    }

    /**
     * Tests the ..url :: directive
     */
    public function testUrl()
    {
        $contents = file_get_contents($this->targetFile('index.html'));

        $this->assertContains('"magic-link.html', $contents);
        $this->assertContains('Another page', $contents);
    }

    /**
     * Tests the links
     */
    public function testLinks()
    {
        $contents = file_get_contents($this->targetFile('subdir/test.html'));

        $this->assertContains('"../to/resource"', $contents);
        $this->assertContains('"http://absolute/"', $contents);

        $this->assertContains('"http://google.com"', $contents);
        $this->assertContains('"http://yahoo.com"', $contents);

        $this->assertEquals(2, substr_count($contents, 'http://something.com'));
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

    /**
     * Testing references to other documents
     */
    public function testReferences()
    {
        $contents = file_get_contents($this->targetFile('introduction.html'));

        $this->assertContains('<a href="index.html#toc">Index, paragraph toc</a>', $contents);
        $this->assertContains('<a href="index.html">Index</a>', $contents);
        $this->assertContains('<a href="index.html">Summary</a>', $contents);
        
        $contents = file_get_contents($this->targetFile('subdir/test.html'));

        $this->assertContains('"../index.html"', $contents);
    }

    /**
     * Testing wrapping sub directive
     */
    public function testSubDirective()
    {
        $contents = file_get_contents($this->targetFile('subdirective.html'));

        $this->assertEquals(2, substr_count($contents, '<div class="note">'));
        $this->assertEquals(2, substr_count($contents, '<li>'));
        $this->assertContains('</div>', $contents);
        $this->assertEquals(2, substr_count($contents, '</li>'));
        $this->assertEquals(1, substr_count($contents, '<ul>'));
        $this->assertEquals(1, substr_count($contents, '</ul>'));
        $this->assertContains('<p>This is a simple note!</p>', $contents);
        $this->assertContains('<h2>There is a title here</h2>', $contents);
    }

    /**
     * Test that redirection-title worked
     */
    public function testRedirectionTitle()
    {
        $contents = file_get_contents($this->targetFile('magic-link.html'));
        $this->assertNotContains('redirection', $contents);

        $contents = file_get_contents($this->targetFile('index.html'));
        $this->assertContains('"subdirective.html">See also', $contents);
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
