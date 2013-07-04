<?php

namespace Gregwar\RST;

class Environment
{
    /**
     * Letters used as separators for titles and horizontal line
     */
    public static $letters = array(
        '=' => 1,
        '-' => 2,
        '~' => 3,
        '*' => 4
    );

    public static $tableLetter = '=';

    // Current file name
    protected $currentFileName = null;
    protected $currentDirectory = '.';
    protected $targetDirectory = '.';

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
        foreach (self::$letters as $letter => $level) {
            $this->levels[$level] = 1;
            $this->counters[$level] = 0;
        }
    }

    public function setMetas($metas)
    {
        $this->metas = $metas;
    }

    /**
     * Resolves a reference URL
     */
    public function resolve($url)
    {
        $url = $this->canonicalUrl($url);

        if ($this->metas) {
            $entry = $this->metas->get($url);
            $entry['url'] = $this->relativeUrl('/'.$entry['url']);
            return $entry;
        } else {
            return null;
        }
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
        foreach (self::$letters as $letter => $currentLevel) {
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
        $this->anonymous[] = $name;
    }

    /**
     * Get a link value
     */
    public function getLink($name)
    {
        $name = trim(strtolower($name));
        if (isset($this->links[$name])) {
            return $this->links[$name];
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
                    return $this->getDirName() . '/' .$url;
                } else {
                    return $url;
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
}
