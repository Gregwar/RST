<?php

namespace Gregwar\RST;

class Environment
{
    // Variables of the document
    protected $variables = array();

    // Links
    protected $links = array();

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
            $name = $this->anonymous;
        }

        $this->links[$name] = $url;
    }

    /**
     * Set the current anonymous links name
     */
    public function setAnonymousName($name)
    {
        $this->anonymous = trim(strtolower($name));
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
