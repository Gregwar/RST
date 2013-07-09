<?php

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Parser;

/**
 * Sets the document URL
 */
class Url extends Directive
{
    public function getName()
    {
        return 'url';
    }

    public function processAction(Parser $parser, $variabe, $data, array $options)
    {
        $environment = $parser->getEnvironment();
        $environment->setUrl(trim($data));
    }
}
