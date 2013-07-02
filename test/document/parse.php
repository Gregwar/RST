<?php

include('../../autoload.php');

use Gregwar\RST\Parser;

$parser = new Parser;
$document = $parser->parse(file_get_contents('document.rst'));

echo $document->renderDocument();
