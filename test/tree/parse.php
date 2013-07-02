<?php

include('../../autoload.php');

use Gregwar\RST\DirectoryParser;

$parser = new DirectoryParser;
$parser->parse('input', 'output');
