<?php

namespace Gregwar\RST;

class Parser
{
    const STATE_BEGIN = 0;
    const STATE_NORMAL = 1;
    const STATE_DIRECTIVE = 2;
    const STATE_BLOCK = 3;
    const STATE_TITLE = 4;
    const STATE_LIST = 5;
    const STATE_SEPARATOR = 6;
    const STATE_CODE = 7;

    // Current state
    protected $state;

    // Current document
    protected $document;

    // The buffer is an array containing current lines that are parsed
    protected $buffer;

    // Current level of special lines (==== and so)
    protected $specialLetter;

    // Current directive to be applied on next node
    protected $directive = false;

    // Current directives
    protected $directives = array();

    // Environment
    protected $environment = null;

    // Is the current node code ?
    protected $isCode = false;

    public function __construct($metas = null, $environment = null, array $directives = array(), $factory = null)
    {
        $this->environment = $environment ?: new Environment;
        if ($metas) {
            $this->environment->setMetas($metas);
        }

        if ($factory == null) {
            $factory = new \Gregwar\RST\HTML\Factory;
        }
        $this->factory = $factory;

        if (!$directives) {
            $this->initDirectives();
        } else {
            $this->directives = $directives;
        }
    }

    /**
     * Get a parser with the same environment that this one
     *
     * @return Parser a new parser with the same environment
     */
    public function getSubParser()
    {
        return new Parser(null, $this->environment, $this->directives, $this->factory);
    }

    /**
     * Try to parse a link definition
     */
    public function parseLink($line)
    {
        // Links
        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match)) {
            $this->environment->setLink($match[1], $match[2]);
            return true;
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match)) {
            $url = $match[1];
            $this->environment->setLink('_', $url);
            return true;
        }

        // Anchor link 
        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match)) {
            $anchor = $match[1];
            $this->document->addNode(new RawNode('<a id="'.$anchor.'"></a>'));
            $this->environment->setLink($match[1], '#'.$anchor);
            return true;
        }

        return false;
    }

    /**
     * Initializing built-in directives
     */
    public function initDirectives()
    {
        $directives = $this->factory->getDirectives();

        foreach ($directives as $name => $directive) {
            $this->registerDirective($directive);
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
     * Get the current factory
     *
     * @return Factory the factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Register a new directive handler
     *
     * @param $directive a directive handler
     */
    protected function registerDirective(Directive $directive)
    {
        $this->directives[$directive->getName()] = $directive;
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
            if (substr($lastLine, -2) == '::') {
                if (trim($lastLine) == '::') {
                    array_pop($this->buffer);
                } else {
                    $this->buffer[count($this->buffer)-1] = substr($lastLine, 0, -1);
                }
                return true;
            }
        }

        return false;
    }

    protected function init()
    {
        $this->specialLetter = false;
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

        if (!isset(Environment::$letters[$letter])) {
            return false;
        }

        for ($i=1; $i<strlen($line); $i++) {
            if ($line[$i] != $letter) {
                return false;
            }
        }

        return $letter;
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
        $node = $this->factory->createNode('ListNode');
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

        $this->isCode = false;

        if ($this->buffer) {
            switch ($this->state) {
            case self::STATE_TITLE:
                $data = implode("\n", $this->buffer);
                $this->environment->createTitle($this->specialLetter);
                $node = $this->factory->createNode('TitleNode', $this->createSpan($data), Environment::$letters[$this->specialLetter]);
                break;
            case self::STATE_SEPARATOR:
                $node = $this->factory->createNode('SeparatorNode', Environment::$letters[$this->specialLetter]);
                break;
            case self::STATE_CODE:
                $node = $this->factory->createNode('CodeNode', $this->buffer);
                break;
            case self::STATE_BLOCK:
                $node = $this->factory->createNode('QuoteNode', $this->buffer);
                break;
            case self::STATE_LIST:
                $node = $this->createListNode();
                break;
            case self::STATE_NORMAL:
                $this->isCode = $this->prepareCode();
                $node = $this->factory->createNode('ParagraphNode', $this->createSpan($this->buffer));
                break;
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
        switch ($this->state) {
        case self::STATE_BEGIN:
            if (trim($line)) {
                if ($this->isBlockLine($line)) {
                    if ($this->isCode) {
                        $this->state = self::STATE_CODE;
                    } else {
                        $this->state = self::STATE_BLOCK;
                    }
                    return false;
                } else if ($this->isDirective($line)) {
                    $this->state = self::STATE_DIRECTIVE;
                    $this->buffer = array();
                    $this->flush();
                    $this->initDirective($line);
                } else if ($this->parseLink($line)) {
                    return true;
                } else {
                    $this->state = self::STATE_NORMAL;
                    return false;
                }
            }
            break;

        case self::STATE_NORMAL:
            if (trim($line)) {
                $specialLetter = $this->isSpecialLine($line);

                if ($specialLetter) {
                    $this->specialLetter = $specialLetter;
                    $lastLine = array_pop($this->buffer);

                    if ($lastLine) {
                        $this->buffer = array($lastLine);
                        $this->state = self::STATE_TITLE;
                    } else {
                        $this->buffer[] = $line;
                        $this->state = self::STATE_SEPARATOR;
                    }
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
                } else {
                    if (!$this->isComment($line)) {
                        $this->buffer[] = $line;
                    }
                }
            } else {
                if ($this->isList()) {
                    $this->state = self::STATE_LIST;
                }
                $this->flush();
                $this->state = self::STATE_BEGIN;
            }
            break;

        case self::STATE_BLOCK:
        case self::STATE_CODE:
            if (!$this->isBlockLine($line)) {
                $this->flush();
                $this->state = self::STATE_BEGIN;
                return false;
            } else {
                $this->buffer[] = $line;
            }
            break;

        case self::STATE_DIRECTIVE:
            if (!$this->directiveAddOption($line)) {
                if ($this->isDirective($line)) {
                    $this->flush();
                    $this->initDirective($line);
                } else {
                    $this->state = self::STATE_BEGIN;
                    return false;
                }
            }
            break;

        default:
            throw new \Exception('Parser ended in an unexcepted state');
        }

        return true;
    }

    /**
     * Include all files described in $document and returns the new string of the given
     * document with includes processed
     */
    public function includeFiles($document)
    {
        $parser = $this;

        return preg_replace_callback('/\n\.\. include:: (.+)\n/', function($match) use ($parser) {
            return $parser->includeFiles(file_get_contents($match[1]));
        }, $document);
    }

    /**
     * Process all the lines of a document string
     *
     * @param $document the string (content) of the document
     */
    protected function parseLines(&$document)
    {
        // Including files
        $document = "\n$document\n";
        $document = $this->includeFiles($document);

        $lines = explode("\n", $document);
        $this->state = self::STATE_BEGIN;

        foreach ($lines as $line) {
            while (!$this->parseLine($line));
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
        $this->document = $this->factory->createNode('Document');
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
        return $this->factory->createNode('Span', $this, $span);
    }
}
