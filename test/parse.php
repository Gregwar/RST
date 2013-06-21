<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Demo</title>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
<body>
<?php

include('../autoload.php');

use Gregwar\RST\Parser;

$parser = new Parser;
$document = $parser->parse(file_get_contents('document.rst'));

echo $document;
?>
</body>
