<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;

abstract class Span extends Node
{
    protected $parser;
    protected $span;
    protected $tokens;
    protected $environment;

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
        $span = preg_replace_callback('/``(.+)``(?!`)/mUsi', function($match) use (&$tokens, $generator) {
            $id = $generator();
            $tokens[$id] = array(
                'type' => 'literal',
                'text' => htmlspecialchars($match[1])
            );

            return $id;
        }, $span);
        
        $environment = $parser->getEnvironment();
        $this->environment = $environment;
        
        // Replacing numbering
        foreach ($environment->getTitleLetters() as $level => $letter) {
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

            $environment->found($section, $url);

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

    /**
     * Processes some data in the context of the span, this will process the
     * **emphasis**, the nbsp, replace variables and end-of-line brs
     */
    public function process($data)
    {
        $self = $this;
        $environment = $this->parser->getEnvironment();

        $span = $this->escape($data);

        // Emphasis
        $span = preg_replace_callback('/\*\*(.+)\*\*/mUsi', function ($matches) use ($self) {
          return $self->strongEmphasis($matches[1]);
        }, $span);
        $span = preg_replace_callback('/\*(.+)\*/mUsi', function ($matches) use ($self) {
          return $self->emphasis($matches[1]);
        }, $span);

        // Nbsp
        $span = preg_replace('/~/', $this->nbsp(), $span);
        
        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        // Adding brs when a space is at the end of a line
        $span = preg_replace('/ \n/', $this->br(), $span);

        return $span;
    }

    /**
     * Renders the span
     */
    public function render()
    {
        $environment = $this->parser->getEnvironment();
        $span = $this->process($this->span);

        // Replacing tokens
        foreach ($this->tokens as $id => $value) {
            switch ($value['type']) {
            case 'literal':
                $span = str_replace($id, $this->literal($value['text']), $span);
                break;
            case 'reference':
                $reference = $environment->resolve($value['section'], $value['url']);
                $link = $this->reference($reference, $value);

                $span = str_replace($id, $link, $span);
                break;
            case 'link':
                if ($value['url']) {
                    $url = $environment->relativeUrl($value['url']);
                } else {
                    $url = $environment->getLink($value['link']);
                }
                $link = $this->link($url, $this->process($value['link']));
                $span = str_replace($id, $link, $span);
                break;
            }
        }

        return $span;
    }

    public function emphasis($text)
    {
        return $text;
    }

    public function strongEmphasis($text)
    {
        return $text;
    }

    public function nbsp()
    {
        return ' ';
    }

    public function br()
    {
        return "\n";
    }

    public function literal($text)
    {
        return $text;
    }

    public function link($url, $title)
    {
        return $title.' ('.$url.')';
    }

    public function escape($span)
    {
        return $span;
    }

    public function reference($reference, $value)
    {
        if ($reference) {
            $text = $value['text'] ?: (isset($reference['title']) ? $reference['title'] : '');
            $link = $this->link($url, trim($text));
        } else {
            $link = $this->link('#', '(unresolved reference)');
        }

        return $link;
    }
}

