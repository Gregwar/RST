<?php

namespace Gregwar\RST\Roles;

use Gregwar\RST\Parser;

interface RoleRenderer
{
    /**
     * @param Role $role
     * @param Parser $parser
     * @return string
     */
    public function render(Role $role, Parser $parser);
}
