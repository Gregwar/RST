<?php

namespace Gregwar\RST;

// Nodes
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Nodes\QuoteNode;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\ListNode;
use Gregwar\RST\Nodes\SeparatorNode;

// Directives
use Gregwar\RST\Directives\Replace;

class Parser
{
    /**
     * Letters used as separators for titles and horizontal line
     */
    public static $letters = array(
        '=' => 1,
        '-' => 2,
        '*' => 3,
        '~' => 4
    );

    // Current document
    protected $document;

    // The buffer is an array containing current lines that are parsed
    protected $buffer;

    // Current level of special lines (==== and so)
    protected $specialLevel;

    // Current directive to be applied on next node
    protected $directive = false;

    // Are we parsing a directive ?
    protected $parsingDirective = false;

    // Current directives
    protected $directives = array();

    // Environment
    protected $environment = null;

    // Is the current node a block ?
    protected $isBlock = false;

    // Is the current node code ?
    protected $isCode = false;

    public function __construct()
    {
        $this->environment = new Environment;
        $this->initDirectives();
    }

    /**
     * Initializing built-in directives
     */
    public function initDirectives()
    {
        $directives = array(
            'replace' => new Replace
        );

        foreach ($directives as $name => $directive) {
            $this->registerDirective($name, $directive);
        }
    }

    /**
     * Get the current environment
     *
     * @return Environment the parser environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Register a new directive handler
     *
     * @param $name a string representing the directive name
     * @param $directive a directive handler
     */
    protected function registerDirective($name, Directive $directive)
    {
        $this->directives[$name] = $directive;
    }

    /**
     * Tells if the current buffer is announcing a block of code
     */
    protected function prepareCode()
    {
        if (!$this->buffer) {
            return false;
        }

        $lastLine = trim($this->buffer[count($this->buffer)-1]);

        if (strlen($lastLine) >= 2) {
            return substr($lastLine, -2) == '::';
        } else {
            return false;
        }
    }

    protected function init()
    {
        $this->isBlock = false;
        $this->specialLevel = 0;
        $this->isCode = $this->prepareCode() || $this->directive;
        $this->buffer = array();
    }

    /**
     * Tell if a line is a special separating line for title and separators,
     * returns the depth of the special line
     */
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
     *
     * @param $line the string line
     * @return an array containing:
     *         - true if the list is ordered, false else
     *         - the depth of the list
     *         - the text of the first line without the tick
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

        if (preg_match('/^((\*)|([\d]+)\.) (.+)$/', trim($line), $match)) {
            return array($line[$i] == '*' ? false : true,
                $depth, $match[4]);
        }

        return false;
    }

    /**
     * Is the current block a list ?
     *
     * @return bool true if the current buffer should be treated as a list
     */
    protected function isList()
    {
        if (!$this->buffer) {
            return false;
        }

        // A buffer is a list if at leas the first line is a list-style
        return $this->parseListLine($this->buffer[0]);
    }

    /**
     * Create a list node from the current buffer
     *
     * @return ListNode a list node containing all list items
     */
    public function createListNode()
    {
            $node = new ListNode();
            $lineInfo = null;
            $listLine = array();
            foreach ($this->buffer as $line) {
                $infos = $this->parseListLine($line);
                if ($infos) {
                    if ($listLine) {
                        $node->addLine($this->createSpan($listLine), $lineInfo[0], $lineInfo[1]);
                    }
                    $listLine = array($infos[2]);
                    $lineInfo = $infos;
                } else {
                    $listLine[] = $line;
                }
            }
            if ($listLine) {
                $node->addLine($this->createSpan($listLine), $lineInfo[0], $lineInfo[1]);
            }
            $node->close();

            return $node;
    }

    /**
     * A line is a code line if it's empty or if it begins with
     * a trimable caracter, for instance:
     *
     *     This is a block because there is a space in the front
     *     of the caracters
     *
     *     This is still part of the block, even if there is an empty line
     *
     * @param $line the line text
     * @return true if the line is still in a block
     */
    protected function isBlockLine($line)
    {
        if (strlen($line)) {
            return !trim($line[0]);
        } else {
            return !trim($line);
        }
    }

    /**
     * Get current directive if the buffer contains one
     *
     * .. |variable| name:: data
     *     :option: value
     *     :otherOption: otherValue
     *
     * @return false if this is not a directive, else an array containing :
     *         - variable: the variable name of the directive
     *         - name: the directive name
     *         - data: the data of the directive
     *         - options: an array of all the options and their values
     */
    protected function initDirective($line)
    {
        if (preg_match('/^\.\. (\|(.+)\| |)(.+):: (.*)$/mUsi', $line, $match)) {
            $this->directive = array(
                'variable' => $match[2],
                'name' => $match[3],
                'data' => $match[4],
                'options' => array()
            );

            return true;
        }

        return false;
    }

    /**
     * Is this line a comment ?
     *
     * @param $line the line
     * @return bool true if it's a comment
     */
    protected function isComment($line)
    {
        return preg_match('/^\.\.(.*)$/mUsi', $line);
    }

    /**
     * Is this line a directive ?
     *
     * @param $line the line
     * @return bool true if it's a directive
     */
    protected function isDirective($line)
    {
        return preg_match('/^\.\. (\|(.+)\| |)(.+):: (.*)$/mUsi', $line);
    }

    /**
     * Try to add an option line to the current directive, returns true if sucess
     * and false if failure
     */
    protected function directiveAddOption($line)
    {
        if (preg_match('/^([ ]+):(.+): (.+)$/mUsi', $line, $match)) {
            $this->directive['options'][$match[2]] = $match[3];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Flushes the current buffer to create a node
     */
    protected function flush()
    {
        $node = null;

        if ($this->buffer) {
            if ($this->specialLevel) {
                $data = implode("\n", $this->buffer);
                if ($data) {
                    $node = new TitleNode($data, $this->specialLevel);
                } else {
                    $node = new SeparatorNode;
                }
            } else if ($this->isBlock) {
                if ($this->isCode) {
                    $node = new CodeNode($this->buffer);
                } else {
                    $node = new QuoteNode($this->buffer);
                }
            } else {
                if ($this->isList()) {
                    $node = $this->createListNode();
                } else {
                    $node = new Node($this->createSpan($this->buffer));
                }
            }
        }

        if ($this->directive) {
            $name = $this->directive['name'];

            if (isset($this->directives[$name])) {
                $currentDirective = $this->directives[$name];
                $currentDirective->process($this, $node, $this->directive['variable'], $this->directive['data'], $this->directive['options']);
                $node = null;
            } else {
                throw new \Exception('Unknown directive: '.$name);
            }
        }

        $this->directive = null;

        if ($node) {
            $this->document->addNode($node);
        }
        
        $this->init();
    }

    /**
     i* Get the current document
     *
     * @return Document the document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Process one line
     *
     * @param $line the line string
     */
    protected function parseLine(&$line)
    {
        if (!$this->parsingDirective) {
            if ($this->isBlockLine($line)) {
                if (!$this->buffer && trim($line)) {
                    $this->isBlock = true;
                }
            } else {
                if ($this->isBlock) {
                    $this->flush();
                }
            }
        }

        if (!$this->isBlock) {
            if (!trim($line)) {
                if ($this->buffer) {
                    $this->flush();
                    $this->parsingDirective = false;
                }
            } else {
                $specialLevel = $this->isSpecialLine($line);

                if ($specialLevel) {
                    $lastLine = array_pop($this->buffer);
                    $this->flush();

                    $this->specialLevel = $specialLevel;
                    $this->buffer = array($lastLine);
                    $this->flush();
                } else {
                    if (!$this->buffer) {
                        if (!$this->parsingDirective) {
                            if ($this->isDirective($line)) {
                                $this->parsingDirective = true;
                                $this->flush();
                                $this->initDirective($line);
                            }
                        } else {
                            if (!$this->directiveAddOption($line)) {
                                if ($this->isDirective($line)) {
                                    $this->flush();
                                    $this->initDirective($line);
                                } else {
                                    $this->parsingDirective = false;
                                }
                            }
                        }
                    }

                    if (!$this->parsingDirective) {
                        if (!$this->isComment($line)) {
                            $this->buffer[] = $line;
                        }
                    }
                }
            }
        } else {
            $this->buffer[] = $line;
        }
    }

    /**
     * Process all the lines of a document string
     *
     * @param $document the string (content) of the document
     */
    protected function parseLines(&$document)
    {
        $lines = explode("\n", $document);

        foreach ($lines as $line) {
            $this->parseLine($line);
        }

        // Document is flushed twice to trigger the directives
        $this->flush();
        $this->flush();
    }

    /**
     * Parse a document and return a Document instance
     *
     * @param $document the contents (string) of the document
     * @return $document the created document
     */
    public function parse(&$document)
    {
        $this->document = new Document;
        $this->init();
        $this->parseLines(trim($document));

        return $this->document;
    }

    /**
     * Create a span, which is a text with inline style
     *
     * @param $span the content string
     * @return Span a span object
     */
    public function createSpan($span)
    {
        return new Span($this, $span);
    }
}
