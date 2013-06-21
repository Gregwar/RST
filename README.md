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

use Gregwar\RST\Parser;

// Loading document
$data = file_get_contents('document.rst');

// Parsing it
$parser = new Parser;
$document = $parser->parse($data);

// Rendering it
echo $document;
```

License
-------

This library is under MIT license
