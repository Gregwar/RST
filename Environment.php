<?php

namespace Gregwar\RST;

class Environment
{
    // Variables of the document
    protected $variables;

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
}
