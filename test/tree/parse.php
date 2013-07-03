<?php

include('../../autoload.php');

use Gregwar\RST\DirectoryParser;

try
{
    $parser = new DirectoryParser;
    $parser->parse('input', 'output');
}
catch (\Exception $exception)
{
    echo "\n";
    echo "Error: ".$exception->getMessage()."\n";
}
