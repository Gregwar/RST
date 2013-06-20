<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
class Replace extends Directive
{
    public function getName()
    {
        return 'replace';
    }

    public function processAction(Parser $parser, $variable, $data, array $options)
    {
        $environment = $parser->getEnvironment();
        $environment->setVariable($variable, $data);
    }
}
