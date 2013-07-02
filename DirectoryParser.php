<?php

namespace Gregwar\RST;

/**
 * A directory parser can parses a whole directory tree and
 * produces an output tree
 */
class DirectoryParser
{
    protected $directory;
    protected $output;

    public function parse($directory, $output = 'output')
    {
        $this->directory = $directory;
        $this->output = $output;

        // Creating output directory if doesn't exists
        if (!is_dir($output)) {
            mkdir($output, 0755, true);
        }
    }
}
