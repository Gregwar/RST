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
    const STATE_TABLE = 8;
    const STATE_COMMENT = 9;

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

    // Allow include directives?
    protected $includeAllowed = true;

    // Behaves like PHP's open_basedir
    protected $includeRoot = '';

    // Is the current node code ?
    protected $isCode = false;

    // Current line
    protected $currentLine = 0;

    // File name
    protected $filename = null;

    public function __construct($environment = null, $kernel = null)
    {
        if ($kernel == null) {
            $kernel = new \Gregwar\RST\HTML\Kernel;
        }
        $this->kernel = $kernel;
        
        $this->environment = $environment ?: $this->kernel->build('Environment');

        $this->initDirectives();
        $this->initReferences();
    }

    /**
     * Get a parser with the same environment that this one
     *
     * @return Parser a new parser with the same environment
     */
    public function getSubParser()
    {
        return new Parser($this->environment, $this->kernel);
    }

    /**
     * Try to parse a link definition
     * 
     * @param string $line
     * @return bool
     */
    public function parseLink($line)
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match)) {
            $this->environment->setLink($match[1], $match[2]);
            return true;
        }

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
            $this->document->addNode($this->kernel->build('Nodes\AnchorNode', $anchor));
            $this->environment->setLink($anchor, '#'.$anchor);
            return true;
        }

        return false;
    }

    /**
     * Initializing built-in directives
     */
    public function initDirectives()
    {
        $directives = $this->kernel->getDirectives();

        foreach ($directives as $name => $directive) {
            $this->registerDirective($directive);
        }
    }

    /**
     * Initializing references
     */
    public function initReferences()
    {
        $references = $this->kernel->getReferences();

        foreach ($references as $reference) {
            $this->environment->registerReference($reference);
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
     * Get the current kernel
     *
     * @return Kernel the kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Register a new directive handler
     *
     * @param Directive $directive a directive handler
     */
    public function registerDirective(Directive $directive)
    {
        $this->directives[$directive->getName()] = $directive;
    }

    /**
     * Tells if the current buffer is announcing a block of code
     * @return bool
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
     * 
     * @param string $line
     * @return bool
     */
    protected function isSpecialLine($line)
    {
        if (strlen($line) < 3) {
            return false;
        }

        $letter = $line[0];

        $environment = $this->environment;
        if (!in_array($letter, $environment::$letters)) {
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
     * Finding the table chars
     * 
     * @param string $line
     * @return array|bool
     */
    protected function findTableChars($line)
    {
        $lineChar = $line[0];
        $spaceChar = null;

        for ($i=0; $i<strlen($line); $i++) {
            if ($line[$i] != $lineChar) {
                if ($spaceChar == null) {
                    $spaceChar = $line[$i];
                } else {
                    if ($line[$i] != $spaceChar) {
                        return false;
                    }
                }
            }
        }

        return array($lineChar, $spaceChar);
    }

    /**
     * If the given line is a table line, this will returns the parts
     * of the given line, i.e the offset of the separators
     *
     * ====================== ========= ===========
     * 0                      23        33
     *
     * +---------------------+---------+-----------+
     *  1                     23        33
     * 
     * @param string $line
     * @return mixed
     */
    protected function parseTableLine($line)
    {
        $header = false;
        $pretty = false;
        $line = trim($line);

        if (!strlen($line)) {
            return false;
        }

        // Finds the table chars
        $chars = $this->findTableChars($line);

        if (!$chars) {
            return false;
        }

        if ($chars[0] == Environment::$prettyTableJoint && $chars[1] == Environment::$prettyTableLetter) {
            $pretty = true;
            $chars = array(Environment::$prettyTableLetter, Environment::$prettyTableJoint);
        } else if ($chars[0] == Environment::$prettyTableJoint && $chars[1] == Environment::$prettyTableHeader) {
            $pretty = true;
            $header = true;
            $chars = array(Environment::$prettyTableHeader, Environment::$prettyTableJoint);
        } else {
            if (!($chars[0] == Environment::$tableLetter && $chars[1] == ' ')) {
                return false;
            }
        }

        $parts = array();
        $separator = false;
        // Crawl the line to match those chars
        for ($i=0; $i<strlen($line); $i++) {
            if ($line[$i] == $chars[0]) {
                if (!$separator) {
                    $parts[] = $i;
                    $separator = true;
                }
            } else {
                if ($line[$i] == $chars[1]) {
                    $separator = false;
                } else {
                    return false;
                }
            }
        }

        if (count($parts) > 1) {
            return array(
                $header,
                $pretty,
                $parts
            );
        }

        return false;
    }

    /**
     * Parses a list line
     *
     * @param string $line the string line
     * @return array containing:
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

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match)) {
            return array(
                'prefix' => $line[$i],
                'ordered' => ($line[$i] == '*' || $line[$i] == '-') ? false : true,
                'depth' => $depth,
                'text' => array($match[4])
            );
        }

        return false;
    }

    /**
     * Is the given line a list line ?
     *
     * @param string $line
     * @return bool true if the given line is a list line
     */
    protected function isListLine($line)
    {
        // A buffer is a list if at least the first line is a list-style
        $listLine = $this->parseListLine($line);

        if ($listLine) {
            return $listLine['depth'] == 0 || !$this->isCode;
        }

        return false;
    }

    protected $lineInfo;
    protected $listLine;
    protected $listFlow;

    /**
     * Push a line to the current list node buffer
     * 
     * @param string $line
     * @param bool $flush
     * @return bool
     */
    public function pushListLine($line, $flush = false)
    {
        if (trim($line)) {
            $infos = $this->parseListLine($line);

            if ($infos) {
                if ($this->lineInfo) {
                    $this->lineInfo['text'] = $this->createSpan($this->lineInfo['text']);
                    $this->buffer->addLine($this->lineInfo);
                }
                $this->lineInfo = $infos;
            } else {
                if ($this->listFlow || $line[0] == ' ') {
                    $this->lineInfo['text'][] = $line;
                } else {
                    $flush = true;
                }
            }
            $this->listFlow = true;
        } else {
            $this->listFlow = false;
        }

        if ($flush) {
            if ($this->lineInfo) {
                $this->lineInfo['text'] = $this->createSpan($this->lineInfo['text']);
                $this->buffer->addLine($this->lineInfo);
                $this->lineInfo = null;
            }

            return false;
        }

        return true;
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
     * @param string $line the line text
     * @return bool true if the line is still in a block
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
     * @param string $line
     * @return false if this is not a directive, else an array containing :
     *         - variable: the variable name of the directive
     *         - name: the directive name
     *         - data: the data of the directive
     *         - options: an array of all the options and their values
     */
    protected function initDirective($line)
    {
        if (preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line, $match)) {
            $this->directive = array(
                'variable' => $match[2],
                'name' => $match[3],
                'data' => trim($match[4]),
                'options' => array()
            );

            return true;
        }

        return false;
    }

    /**
     * Is this line a comment ?
     *
     * @param string $line the line
     * @return bool true if it's a comment
     */
    protected function isComment($line)
    {
        return preg_match('/^\.\. (.*)$/mUsi', $line);
    }

    /**
     * Is this line a directive ?
     *
     * @param string $line the line
     * @return bool true if it's a directive
     */
    protected function isDirective($line)
    {
        return preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::(.*)$/mUsi', $line);
    }

    /**
     * Try to add an option line to the current directive, returns true if sucess
     * and false if failure
     * 
     * @param string $line
     */
    protected function directiveAddOption($line)
    {
        if (preg_match('/^(\s+):(.+): (.*)$/mUsi', $line, $match)) {
            $this->directive['options'][$match[2]] = trim($match[3]);
            return true;
        } else if (preg_match('/^(\s+):(.+):(\s*)$/mUsi', $line, $match)) {
            $value = trim($match[3]);
            $this->directive['options'][$match[2]] = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets the current directive
     * 
     * @return Directive
     */
    protected function getCurrentDirective()
    {
        if (!$this->directive) {
            $this->getEnvironment()->getErrorManager()->error('Asking for current directive, but there is not');
        }

        $name = $this->directive['name'];
        if (isset($this->directives[$name])) {
            return $this->directives[$name];
        } else {
            $message = 'Unknown directive: '.$name;
            $message .= ' in '.$this->getFilename().' line '.$this->getCurrentLine();
            $this->getEnvironment()->getErrorManager()->error($message);
            return null;
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
                $level = $this->environment->getLevel($this->specialLetter);
                $token = $this->environment->createTitle($level);
                $node = $this->kernel->build('Nodes\TitleNode', $this->createSpan($data), $level, $token);
                break;
            case self::STATE_SEPARATOR:
                $level = $this->environment->getLevel($this->specialLetter);
                $node = $this->kernel->build('Nodes\SeparatorNode', $level);
                break;
            case self::STATE_CODE:
                $node = $this->kernel->build('Nodes\CodeNode', $this->buffer);
                break;
            case self::STATE_BLOCK:
                $node = $this->kernel->build('Nodes\QuoteNode', $this->buffer);
                $data = $node->getValue();
                $subParser = $this->getSubParser();
                $document = $subParser->parseLocal($data);
                $node->setValue($document);
                break;
            case self::STATE_LIST:
                $this->pushListLine(null, true);
                $node = $this->buffer;
                break;
            case self::STATE_TABLE:
                $node = $this->buffer;
                $node->finalize($this);
                break;
            case self::STATE_NORMAL:
                $this->isCode = $this->prepareCode();
                $node = $this->kernel->build('Nodes\ParagraphNode', $this->createSpan($this->buffer));
                break;
            }
        }

        if ($this->directive) {
            $currentDirective = $this->getCurrentDirective();
            if ($currentDirective) {
                $currentDirective->process($this, $node, $this->directive['variable'], $this->directive['data'], $this->directive['options']);
            }
            $node = null;
        }

        $this->directive = null;

        if ($node) {
            $this->document->addNode($node);
        }

        $this->init();
    }

    /**
     * Get the current document
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
     * @param string $line the line string
     */
    protected function parseLine(&$line)
    {
        switch ($this->state) {
        case self::STATE_BEGIN:
            if (trim($line)) {
                if ($this->isListLine($line)) {
                    $this->state = self::STATE_LIST;
                    $this->buffer = $this->kernel->build('Nodes\ListNode');
                    $this->lineInfo = null;
                    $this->listFlow = true;
                    return false;
                } else if ($this->isBlockLine($line)) {
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
                } else if ($parts = $this->parseTableLine($line)) {
                    $this->state = self::STATE_TABLE;
                    $this->buffer = $this->kernel->build('Nodes\TableNode', $parts);
                } else {
                    $this->state = self::STATE_NORMAL;
                    return false;
                }
            }
            break;

        case self::STATE_LIST:
            if (!$this->pushListLine($line)) {
                $this->flush();
                $this->state = self::STATE_BEGIN;
                return false;
            }
            break;

        case self::STATE_TABLE:
            if (!trim($line)) {
                $this->flush();
                $this->state = self::STATE_BEGIN;
            } else {
                $parts = $this->parseTableLine($line);

                if (!$this->buffer->push($parts, $line)) {
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
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
                    if ($this->isDirective($line)) {
                        $this->flush();
                        $this->state = self::STATE_BEGIN;
                        return false;
                    }
                    if ($this->isComment($line)) {
                        $this->flush();
                        $this->state = self::STATE_COMMENT;
                    } else {
                        $this->buffer[] = $line;
                    }
                }
            } else {
                $this->flush();
                $this->state = self::STATE_BEGIN;
            }
            break;
        
        case self::STATE_COMMENT:
            $isComment = false;

            if (!$this->isComment($line) && (!trim($line) || $line[0] != ' ')) {
                $this->state = self::STATE_BEGIN;
                return false;
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
                    $directive = $this->getCurrentDirective();
                    $this->isCode = $directive ? $directive->wantCode() : false;
                    $this->state = self::STATE_BEGIN;
                    return false;
                }
            }
            break;

        default:
            $this->getEnvironment()->getErrorManager()->error('Parser ended in an unexcepted state');
        }

        return true;
    }

    /**
     * Is this file allowed to be included?
     *
     * @param $path
     * @return bool
     */
    public function includeFileAllowed($path)
    {
        if (!$this->includeAllowed) {
            return false;
        }
        if (!@is_readable($path)) {
            return false;
        }
        if (empty($this->includeRoot)) {
            return true;
        }
        $real = realpath($path);
        foreach (explode(':', $this->includeRoot) as $root) {
            if (strpos($real, $root) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Include all files described in $document and returns the new string of the given
     * document with includes processed
     */
    public function includeFiles($document)
    {
        $environment = $this->getEnvironment();
        $parser = $this;

        return preg_replace_callback('/^\.\. include:: (.+)$/m', function($match) use ($parser, $environment) {
            $path = $environment->absoluteRelativePath($match[1]);
            if ($parser->includeFileAllowed($path)) {
                return $parser->includeFiles(file_get_contents($path));
            } else {
                return '';
            }
        }, $document);
    }

    /**
     * Process all the lines of a document string
     *
     * @param string $document the string (content) of the document
     */
    protected function parseLines($document)
    {
        // Including files
        $document = str_replace("\r\n", "\n", $document);
        $document = "\n$document\n";
        $document = $this->includeFiles($document);
        
        // Removing UTF-8 BOM
        $bom = "\xef\xbb\xbf";
        $document = str_replace($bom, '', $document);

        $lines = explode("\n", $document);
        $this->state = self::STATE_BEGIN;

        foreach ($lines as $n => $line) {
            $this->currentLine = $n;
            while (!$this->parseLine($line));
        }

        // Document is flushed twice to trigger the directives
        $this->flush();
        $this->flush();
    }

    /**
     * Parse a document and return a Document instance
     *
     * @param string $document The contents (string) of the document
     * @return Document The created document
     */
    public function parse($document)
    {
        $this->getEnvironment()->reset();

        return $this->parseLocal($document);
    }

    /**
     * @param string $document
     * @return Document The created document
     */
    public function parseLocal($document)
    {
        $this->document = $this->kernel->build('Document', $this->environment);
        $this->init();
        $this->parseLines(trim($document));

        foreach ($this->directives as $name => $directive) {
            $directive->finalize($this->document);
        }

        return $this->document;
    }

    /**
     * Parses a given file and return a Document instance
     *
     * @param string $file the file name to parse
     * @return Document $document the document instance
     */
    public function parseFile($file)
    {
        $this->filename = $file;
        return $this->parse(file_get_contents($file));
    }

    /**
     * Gets the current filename
     */
    public function getFilename()
    {
        return $this->filename ?: '(unknown)';
    }

    /**
     * Gets the current line
     */
    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    /**
     * Create a span, which is a text with inline style
     *
     * @param $span the content string
     * @return Span a span object
     */
    public function createSpan($span)
    {
        return $this->kernel->build('Span', $this, $span);
    }

    /**
     * @return bool
     */
    public function getIncludeAllowed()
    {
        return $this->includeAllowed;
    }

    /**
     * @return string
     */
    public function getIncludeRoot()
    {
        return $this->includeRoot;
    }

    /**
     * Allow/disallow includes, or restrict them to a directory
     *
     * @param bool $allow
     * @param string $directory
     * @return self
     */
    public function setIncludePolicy($allow, $directory = null)
    {
        $this->includeAllowed = !empty($allow);
        if ($directory !== null) {
            $this->includeRoot = (string) $directory;
        }
        return $this;
    }
}
