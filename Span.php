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

        $tokenId = 0;
        $prefix = mt_rand().'|'.time();
        $generator = function() use ($prefix, &$tokenId) {
            $tokenId++;
            return sha1($prefix.'|'.$tokenId);
        };
        
        // Replacing literal with tokens
        $tokens = array();
        $span = preg_replace_callback('/``(.+)``/mUsi', function($match) use (&$tokens, $generator) {
            $id = $generator();
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
        $span = preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function($match) use (&$environment, $generator, &$tokens) {
            $section = $match[1];
            $url = $match[2];
            $id = $generator();
            $anchor = null;
            
            $text = null;
            if (preg_match('/^(.+)<(.+)>$/mUsi', $url, $match)) {
                $text = $match[1];
                $url = $match[2];
            }

            if (preg_match('/^(.+)#(.+)$/mUsi', $url, $match)) {
                $url = $match[1];
                $anchor = $match[2];
            }

            $tokens[$id] = array(
                'type' => 'reference',
                'section' => $section,
                'url' => $url,
                'text' => $text,
                'anchor' => $anchor
            );

            $environment->addDependency($url);

            return $id;
        }, $span);

        // Link callback
        $linkCallback = function($match) use ($environment, $generator, &$tokens) {
            $link = $match[2] ?: $match[4];
            $id = $generator();
            $next = $match[5];
            $url = null;

            if (preg_match('/^(.+) <(.+)>$/mUsi', $link, $match)) {
                $link = $match[1];
                $environment->setLink($link, $match[2]);
                $url = $match[2];
            }

            $tokens[$id] = array(
                'type' => 'link',
                'link' => $link,
                'url' => $url
            );

            return $id.$next;
        };
        
        // Replacing anonymous links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))__([^a-z0-9]{1}|$)/mUsi', $linkCallback, $span);

        // Replacing links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))_([^a-z0-9]{1}|$)/mUsi', $linkCallback, $span);
        
        $this->tokens = $tokens;
        $this->parser = $parser;
        $this->span = $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}

