<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\SubDirective;
use Gregwar\RST\Parser;

class Toctree extends SubDirective
{
    public function getName()
    {
        return 'toctree';
    }

    public function processSub(Parser $parser, $document, $variable, $data, array $options)
    {
        $nodes = $document->getNodes();
        $environment = $parser->getEnvironment();
        $factory = $parser->getFactory();
        $files = array();

        foreach ($nodes as $node) {
            foreach (explode("\n", $node->getValue()) as $file) {
                $file = trim($file);
                if ($file) {
                    $environment->addDependency($file);
                    $files[] = $file;
                }
            }
        }

        return $factory->createNode('TocNode', $files, $environment, $options);
    }
}
