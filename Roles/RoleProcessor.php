<?php

namespace Gregwar\RST\Roles;

use Gregwar\RST\Document;
use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Exception\InvalidContentException;

/**
 * Roles occurs mid-text, for instance :abbr:`PHP (PHP: Hypertext Preprocessor)`. Each processor processes the content
 * of a specific role and returns an instance of a Role.
 */
interface RoleProcessor
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $content
     * @param Parser $parser
     * @return Role
     * @throws InvalidContentException
     */
    public function process($content, Parser $parser);

    /**
     * Allows reading information from eg. the document and the environment to finalize a role.
     *
     * @param Role $role
     * @param Document $document
     * @return void
     */
    public function finalize(Role $role, Document $document);
}
