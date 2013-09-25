<?php

include('../../autoload.php');

use Gregwar\RST\Parser;
use Gregwar\RST\LaTeX\Kernel;

$parser = new Parser(null, new Kernel);
$document = $parser->parse(file_get_contents('document.rst'));

echo $document->renderDocument();
