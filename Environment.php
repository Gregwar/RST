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

    // Variables of the document
    protected $variables = array();

    // Links
    protected $links = array();

    // Level counters
    protected $levels;

    // Anonymous links stack
    protected $anonymous = array();

    public function __construct()
    {
        foreach (self::$letters as $letter => $level) {
            $this->levels[$level] = 1;
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
    public function createTitle($letter)
    {
        $level = self::$letters[$letter];
        foreach (self::$letters as $letter => $currentLevel) {
            if ($currentLevel > $level) {
                $this->levels[$level] = 1;
            }
        }
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
}
