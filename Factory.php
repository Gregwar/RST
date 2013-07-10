<?php

namespace Gregwar\RST;

abstract class Factory
{
    abstract function getName();

    public function getClass($name)
    {
        return 'Gregwar\RST\\'.$this->getName().'\\'.$name;
    }

    public function createNode($name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $class = $this->getClass($name);

        if ($class) {
            return new $class($arg1, $arg2, $arg3, $arg4);
        }

        return null;
    }

    public function getDirectives()
    {
        return array(
            new Directives\Replace,
            new Directives\Toctree
        );
    }
}
