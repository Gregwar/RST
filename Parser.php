<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\ListNode;
use Gregwar\RST\Nodes\SeparatorNode;

class Parser
{
    public static $letters = array(
        '=' => 1,
        '-' => 2,
        '*' => 3,
        '~' => 4
    );

    protected $node;
    protected $document;
    protected $buffer;
    protected $specialLevel;

    protected function init()
    {
        $this->buffer = array();
    }

    protected function isSpecialLine($line)
    {
        if (strlen($line) < 3) {
            return false;
        }

        $letter = $line[0];

        if (!isset(self::$letters[$letter])) {
            return false;
        }

        for ($i=1; $i<strlen($line); $i++) {
            if ($line[$i] != $letter) {
                return false;
            }
        }

        return self::$letters[$letter];
    }

    /**
     * Parses a list line
     */
    protected function parseListLine($line)
    {
        $depth = 0;
        for ($i=0; $i<strlen($line); $i++) {
            $char = $line[$i];

            if ($char == ' ') {
                $depth++;
            } else if ($char == "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*)|([\d]+\.)) /', trim($line))) {
            return array($line[$i] == '*' ? false : true,
                $depth);
        }

        return false;
    }

    /**
     * Is the current block a list ?
     */
    protected function isList()
    {
        if (!$this->buffer) {
            return false;
        }

        return $this->parseListLine($this->buffer[0]);
    }

    /**
     * Flushes the current node
     */
    protected function flush()
    {
        if (!$this->buffer) {
            return;
        }

        $node = null;

        if ($this->specialLevel) {
            $data = implode("\n", $this->buffer);
            if ($data) {
                $node = new TitleNode($data, $this->specialLevel);
            } else {
                $node = new SeparatorNode;
            }
        } else {
            if ($this->isList()) {
                $node = new ListNode();
                $lineInfo = null;
                $listLine = array();
                foreach ($this->buffer as $line) {
                    $infos = $this->parseListLine($line);
                    if ($infos) {
                        if ($listLine) {
                            $node->addLine($listLine, $lineInfo[0], $lineInfo[1]);
                        }
                        $listLine = array(preg_replace('/^((\*)|([\d]+\.)) /', '', trim($line)));
                        $lineInfo = $infos;
                    } else {
                        $listLine[] = $line;
                    }
                }
                if ($listLine) {
                    $node->addLine($listLine, $lineInfo[0], $lineInfo[1]);
                }
                $node->close();
            } else {
                $data = implode("\n", $this->buffer);
                $node = new Node($data);
            }
        }

        if ($node) {
            $this->document->addNode($node);
        }

        $this->buffer = array();
        $this->specialLevel = 0;
    }

    /**
     * Process one line
     */
    protected function parseLine(&$line)
    {
        if (!trim($line)) {
            $this->flush();
        } else {
            $specialLevel = $this->isSpecialLine($line);

            if ($specialLevel) {
                $lastLine = array_pop($this->buffer);
                $this->flush();

                $this->specialLevel = $specialLevel;
                $this->buffer = array($lastLine);
                $this->flush();
            } else {
                $this->buffer[] = $line;
            }
        }
    }

    /**
     * Process all the lines of a document string
     */
    protected function parseLines(&$document)
    {
        $lines = explode("\n", $document);

        foreach ($lines as $line) {
            $this->parseLine($line);
        }
        
        $this->flush();
    }

    /**
     * Parse a document and return a Document instance
     */
    public function parse(&$document)
    {
        $this->document = new Document;
        $this->init();
        $this->parseLines(trim($document));

        return $this->document;
    }
}
