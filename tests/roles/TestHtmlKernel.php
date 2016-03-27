<?php

namespace Gregwar\RST\Tests\Roles;

use Gregwar\RST\Directive;
use Gregwar\RST\HTML\Kernel;
use Gregwar\RST\Reference;
use Gregwar\RST\Roles\RoleConfiguration;

class TestHtmlKernel extends Kernel
{
    /** @var Reference[] */
    public $references = array();

    /** @var Directive[] */
    public $directives = array();

    /** @var RoleConfiguration[] */
    public $roleConfigurations = array();

    public function getReferences()
    {
        return array_merge($this->references, parent::getReferences());
    }

    public function getDirectives()
    {
        return array_merge($this->directives, parent::getDirectives());
    }

    public function getRoleConfigurations()
    {
        return array_merge($this->roleConfigurations, parent::getRoleConfigurations());
    }
}
