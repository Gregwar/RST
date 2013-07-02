<?php

namespace Gregwar\RST;

abstract class Span
{
    protected $parser;
    protected $span;
    protected $tokens;
    
    abstract public function render();

    public function __construct(Parser $parser, $span)
    {
        if (is_array($span)) {
            $span = implode("\n", $span);
        }
        
        // Replacing literal with tokens
        $prefix = sha1(time().'/'.mt_rand());
        $tokens = array();
        $span = preg_replace_callback('/``(.+)``/mUsi', function($match) use (&$tokens, $prefix) {
            $id = $prefix.'/'.sha1($match[1]);
            $tokens[$id] = array(
                'type' => 'literal',
                'text' => htmlspecialchars($match[1])
            );

            return $id;
        }, $span);
        
        $environment = $parser->getEnvironment();

        
        // Replacing numbering
        foreach ($environment::$letters as $letter => $level) {
            $span = preg_replace_callback('/\#\\'.$letter.'/mUsi', function($match) use ($environment, $level) {
                return $environment->getNumber($level);
            }, $span);
        }

        // Signaling anonymous names
        $environment->resetAnonymousStack();
        if (preg_match_all('/(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $matches)) {
            foreach ($matches[2] as $k => $y) {
                $name = $matches[2][$k] ?: $matches[4][$k];
                $environment->pushAnonymous($name);
            }
        }

        // Looking for references to other documents
        $prefix = sha1(time().'/'.mt_rand());
        $span = preg_replace_callback('/:doc:`(.+)`/mUsi', function($match) use (&$environment, $prefix, &$tokens) {
            $url = $match[1];
            $text = null;
            if (preg_match('/^(.+)<(.+)>$/mUsi', $url, $match)) {
                $text = $match[1];
                $url = $match[2];
            }

            $id = $prefix.'/'.sha1($url);
            $tokens[$id] = array(
                'type' => 'reference',
                'url' => $url,
                'text' => $text
            );

            $environment->addDependency($url);

            return $id;
        }, $span);
        
        $this->tokens = $tokens;
        $this->parser = $parser;
        $this->span = $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
