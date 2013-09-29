<?php

include('../../autoload.php');

$parser = new Gregwar\RST\Parser;

$rst = '
Hello world
===========

What is it?
----------
This is a **RST** document!

Where can I get it?
-------------------
You can get it on the `GitHub page <https://github.com/Gregwar/RST>`_
';

$document = $parser->parse($rst);

echo $document;
/* Will output:
<a id="title.1"></a><h1>Hello world</h1>
<a id="title.1.1"></a><h2>What is it?</h2>
<p>This is a <b>RST</b> document!</p>
<a id="title.1.2"></a><h2>Where can I get it?</h2>
<p>You can get it on the <a href="https://github.com/Gregwar/RST">GitHub page</a></p>
*/
