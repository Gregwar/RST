<?php

namespace Gregwar\RST;

abstract class Kernel
{
    /**
     * Get the name of the kernel
     */
    abstract function getName();

    /**
     * Gets the class for the given name
     */
    public function getClass($name)
    {
        return 'Gregwar\RST\\'.$this->getName().'\\'.$name;
    }

    /**
     * Create an instance of some class
     */
    public function build($name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $class = $this->getClass($name);

        if ($class) {
            return new $class($arg1, $arg2, $arg3, $arg4);
        }

        return null;
    }

    /**
     * Gets the available directives
     */
    public function getDirectives()
    {
        return array(
            new Directives\Replace,
            new Directives\Toctree
        );
    }
}
