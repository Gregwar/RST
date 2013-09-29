RST
===

PHP library to parse reStructuredText document

*NOTE: This is a work in progress, the library is not finished at all and then
not documented, if you want to contribute or bring idea, don't hesitate to post
me a message or on the issues tracking*

Usage
-----

The parser can be used this way:

```php
<?php

$parser = new Gregwar\RST\Parser;

// RST document
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

// Parse it
$document = $parser->parse($rst);

// Render it
echo $document;
/* Will output:
<a id="title.1"></a><h1>Hello world</h1>
<a id="title.1.1"></a><h2>What is it?</h2>
<p>This is a <b>RST</b> document!</p>
<a id="title.1.2"></a><h2>Where can I get it?</h2>
<p>You can get it on the <a href="https://github.com/Gregwar/RST">GitHub page</a></p>
*/
```

For more information, you can have a look at `test/document/document.rst` and its result
`test/document/document.html`

License
-------

This library is under MIT license
