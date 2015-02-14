<?php

namespace Gregwar\RST;

class Environment
{
    /**
     * Letters used as separators for titles and horizontal line
     */
    public static $letters = array('=', '-', '~', '*', '^', '"');

    // Error manager
    public $errorManager = null;

    // Table letters
    public static $tableLetter = '=';
    public static $prettyTableLetter = '-';
    public static $prettyTableHeader = '=';
    public static $prettyTableJoint = '+';

    // Title letter for each levels
    protected $currentTitleLevel = 0;
    protected $titleLetters = array();

    // Current file name
    protected $currentFileName = null;
    protected $currentDirectory = '.';
    protected $targetDirectory = '.';
    protected $url = null;

    // References that can be resolved
    protected $references = array();

    // Metas
    protected $metas = null;

    // Dependencies of this document
    protected $dependencies = array();

    // Variables of the document
    protected $variables = array();

    // Links
    protected $links = array();

    // Level counters
    protected $levels = array();
    protected $counters = array();

    // Anonymous links stack
    protected $anonymous = array();

    public function __construct()
    {
        $this->errorManager = new ErrorManager;

        $this->reset();
    }

    /**
     * Puts the environment in a clean state for a new parse, like title level order.
     */
    public function reset()
    {
        $this->titleLetters = array();
        $this->currentTitleLevel = 0;
        $this->levels = array();
        $this->counters = array();

        for ($level=0; $level<16; $level++) {
            $this->levels[$level] = 1;
            $this->counters[$level] = 0;
        }
    }

    public function getErrorManager()
    {
        return $this->errorManager;
    }

    public function setErrorManager(ErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    public function setMetas($metas)
    {
        $this->metas = $metas;
    }

    /**
     * Get my parent metas
     */
    public function getParent()
    {
        if (!$this->currentFileName || !$this->metas) {
            return null;
        }

        $meta = $this->metas->get($this->currentFileName);

        if (!$meta || !isset($meta['parent'])) {
            return null;
        }

        $parent = $this->metas->get($meta['parent']);

        if (!$parent) {
            return null;
        }

        return $parent;
    }

    /**
     * Get the docs involving this document
     */
    public function getMyToc()
    {
        $parent = $this->getParent();

        if ($parent) {
            foreach ($parent['tocs'] as $toc) {
                if (in_array($this->currentFileName, $toc)) {
                    $before = array();
                    $after = $toc;

                    while ($after) {
                        $file = array_shift($after);
                        if ($file == $this->currentFileName) {
                            return array($before, $after);
                        }
                        $before[] = $file;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Registers a new reference
     */
    public function registerReference(Reference $reference)
    {
        $name = $reference->getName();
        $this->references[$name] = $reference;
    }

    /**
     * Resolves a reference
     */
    public function resolve($section, $data)
    {
        if (isset($this->references[$section])) {
            $reference = $this->references[$section];

            return $reference->resolve($this, $data);
        }

        $this->errorManager->error('Unknown reference section '.$section);
    }

    public function found($section, $data)
    {
        if (isset($this->references[$section])) {
            $reference = $this->references[$section];

            return $reference->found($this, $data);
        }

        $this->errorManager->error('Unknown reference section '.$section);
    }

    /**
     * Sets the giving variable to a value
     *
     * @param $variable the variable name
     * @param $value the variable value
     */
    public function setVariable($variable, $value)
    {
        $this->variables[$variable] = $value;
    }

    /**
     * Title level
     */
    public function createTitle($level)
    {
        for ($currentLevel=0; $currentLevel<16; $currentLevel++) {
            if ($currentLevel > $level) {
                $this->levels[$currentLevel] = 1;
                $this->counters[$currentLevel] = 0;
            }
        }

        $this->levels[$level] = 1;
        $this->counters[$level]++;
        $token = array('title');

        for ($i=1; $i<=$level; $i++) {
            $token[] = $this->counters[$i];
        }

        return implode('.', $token);
    }

    /**
     * Get a level number
     */
    public function getNumber($level)
    {
        return $this->levels[$level]++;
    }

    /**
     * Gets the variable value
     *
     * @param $name the variable name
     */
    public function getVariable($variable, $default = null)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }

        return $default;
    }

    /**
     * Set the link url
     */
    public function setLink($name, $url)
    {
        $name = trim(strtolower($name));

        if ($name == '_') {
            $name = array_shift($this->anonymous);
        }

        $this->links[$name] = trim($url);
    }

    /**
     * Resets the anonymous stack
     */
    public function resetAnonymousStack()
    {
        $this->anonymous = array();
    }

    /**
     * Set the current anonymous links name
     */
    public function pushAnonymous($name)
    {
        $this->anonymous[] = trim(strtolower($name));
    }

    /**
     * Get a link value
     */
    public function getLink($name, $relative = true)
    {
        $name = trim(strtolower($name));
        if (isset($this->links[$name])) {
            $link = $this->links[$name];

            if ($relative) {
                return $this->relativeUrl($link);
            }

            return $link;
        }

        return null;
    }

    /**
     * Adds a dependency to the document
     */
    public function addDependency($dependency)
    {
        $dependency = $this->canonicalUrl($dependency);
        $this->dependencies[] = $dependency;
    }

    /**
     * Getting all the dependencies for this environment
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Resolves a relative URL using directories, for instance, if the
     * current directory is "path/to/something", and you want to get the
     * relative URL to "path/to/something/else.html", the result will
     * be else.html. Else, "../" will be added to go to the upper directory
     */
    public function relativeUrl($url)
    {
        // If string contains ://, it is considered as absolute
        if (preg_match('/:\\/\\//mUsi', $url)) {
            return $url;
        }

        // If string begins with "/", the "/" is removed to resolve the
        // relative path
        if (strlen($url) && $url[0] == '/') {
            $url = substr($url, 1);
            if ($this->samePrefix($url)) {
                // If the prefix is the same, simply returns the file name
                $relative = basename($url);
            } else {
                // Else, returns enough ../ to get upper
                $relative = '';

                for ($k=0; $k<$this->getDepth(); $k++) {
                    $relative .= '../';
                }

                $relative .= $url;
            }
        } else {
            $relative = $url;
        }

        return $relative;
    }

    /**
     * Get the depth of the current file name (the number of parent
     * directories)
     */
    public function getDepth()
    {
        return count(explode('/', $this->currentFileName))-1;
    }

    /**
     * Returns true if the given url have the same prefix as the
     * current document
     */
    protected function samePrefix($url)
    {
        $partsA = explode('/', $url);
        $partsB = explode('/', $this->currentFileName);

        $n = count($partsA);
        if ($n != count($partsB)) {
            return false;
        }

        unset($partsA[$n-1]);
        unset($partsB[$n-1]);

        return $partsA == $partsB;
    }

    /**
     * Returns the directory name
     */
    public function getDirName()
    {
        $dirname = dirname($this->currentFileName);

        if ($dirname == '.') {
            return '';
        }

        return $dirname;
    }

    /**
     * Canonicalize a path, a/b/c/../d/e will become
     * a/b/d/e
     */
    protected function canonicalize($url)
    {
        $parts = explode('/', $url);
        $stack = array();

        foreach ($parts as $part) {
            if ($part == '..') {
                array_pop($stack);
            } else {
                $stack[] = $part;
            }
        }

        return implode('/', $stack);
    }

    /**
     * Gets a canonical URL from the given one
     */
    public function canonicalUrl($url)
    {
        if (strlen($url)) {
            if ($url[0] == '/') {
                // If the URL begins with a "/", the following is the 
                // canonical URL
                return substr($url, 1);
            } else {
                // Else, the canonical name is under the current dir
                if ($this->getDirName()) { 
                    return $this->canonicalize($this->getDirName() . '/' .$url);
                } else {
                    return $this->canonicalize($url);
                }
            }
        }

        return null;
    }

    /**
     * Sets the current file name
     */
    public function setCurrentFileName($filename)
    {
        $this->currentFileName = $filename;
    }

    /**
     * Sets the directory of the current parsing
     */
    public function setCurrentDirectory($directory)
    {
        $this->currentDirectory = $directory;
    }

    /**
     * Returns an absolute path for a relative given URL
     */
    public function absoluteRelativePath($url)
    {
        return $this->currentDirectory . '/' . $this->getDirName() . '/' . $this->relativeUrl($url);
    }

    public function setTargetDirectory($directory)
    {
        $this->targetDirectory = $directory;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function getUrl()
    {
        if ($this->url) {
            return $this->url;
        } else {
            return $this->currentFileName;
        }
    }

    public function setUrl($url)
    {
        if ($this->getDirName()) {
            $url = $this->getDirName() . '/' . $url;
        }

        $this->url = $url;
    }

    public function getMetas()
    {
        return $this->metas;
    }

    public function getLevel($letter)
    {
        foreach ($this->titleLetters as $level => $titleLetter) {
            if ($letter == $titleLetter) {
                return $level;
            }
        }

        $this->currentTitleLevel++;
        $this->titleLetters[$this->currentTitleLevel] = $letter;
        return $this->currentTitleLevel;
    }

    public function getTitleLetters()
    {
        return $this->titleLetters;
    }
}
