<?php

include('../../autoload.php');

use Gregwar\RST\Builder;

try
{
    // Build the 'input' files to the 'output' directory
    $builder = new Builder;
    $builder->copy('css', 'css');
    $builder->build('input', 'output');
}
catch (\Exception $exception)
{
    echo "\n";
    echo "Error: ".$exception->getMessage()."\n";
}
