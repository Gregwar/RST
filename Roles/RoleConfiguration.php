<?php

namespace Gregwar\RST\Roles;

class RoleConfiguration
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var RoleProcessor
     */
    private $processor;

    /**
     * @var RoleRenderer
     */
    private $renderer;

    /**
     * @param string $name
     * @param RoleProcessor $processor
     * @param RoleRenderer $renderer
     */
    public function __construct($name, RoleProcessor $processor, RoleRenderer $renderer)
    {
        $this->processor = $processor;
        $this->renderer = $renderer;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return RoleProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return RoleRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
}
