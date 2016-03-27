<?php

namespace Gregwar\RST\Roles;

use Gregwar\RST\Document;
use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Exception\InvalidArgumentException;

class ReferenceProcessor implements RoleProcessor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function process($content, Parser $parser)
    {
        $url = $content;
        $text = null;
        $anchor = null;

        if (preg_match('/^(.+)<(.+)>$/mUsi', $url, $match)) {
            $text = rtrim($match[1]);
            $url = $match[2];
        }

        if (preg_match('/^(.+)#(.+)$/mUsi', $url, $match)) {
            $url = $match[1];
            $anchor = $match[2];
        }

        $parser->getEnvironment()->found($this->getName(), $url);

        return new Reference($url, $text, $anchor);
    }

    public function finalize(Role $role, Document $document)
    {
        InvalidArgumentException::assert('role', $role, 'Gregwar\RST\Roles\Reference');

        /** @var Reference $role */
        $reference = $document->getEnvironment()->resolve($this->getName(), $role->url);
        $role->reference = $reference ? DocReference::fromReferenceArray($reference) : null;
    }
}
