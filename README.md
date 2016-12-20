# RST

[![Build status](https://travis-ci.org/Gregwar/RST.svg?branch=master)](https://travis-ci.org/Gregwar/RST)
[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YUXRLWHQSWS6L)

PHP library to parse reStructuredText document

## Usage

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
/* Will output, in HTML mode:
<a id="title.1"></a><h1>Hello world</h1>
<a id="title.1.1"></a><h2>What is it?</h2>
<p>This is a <b>RST</b> document!</p>
<a id="title.1.2"></a><h2>Where can I get it?</h2>
<p>You can get it on the <a href="https://github.com/Gregwar/RST">GitHub page</a></p>
*/
```

For more information, you can have a look at `test/document/document.rst` and its result
`test/document/document.html`

## Using the builder

The builder is another tool that will parses a whole tree of documents and generates
an output directory containing files.

You can simply use it with:

```php
<?php

$builder = new Gregwar\RST\Builder;
$builder->build('input', 'output');
```

It will parses all the files in the `input` directory, starting with `index.rst` and
scanning for dependencies references and generates you target files in the `output`
directory. Default format is HTML.

You can use those methods on it to customize the build:

* `copy($source, $destination)`: copy the `$source` file or directory to the `$destination`
  file or directory of the build
* `mkdir($directory)`: create the `$directory` in build directory
* `addHook($function)`: adds an hook that will be called after each document is parsed, this
  hook will be called with the `$document` as parameter and can then tweak it as you want
* `addBeforeHook($function)`: adds an hook that will be called before parsing the
  document, the parser will be passed as a parameter

## Abort on error

In some situation you want the build to continue even if there is some errors,
like missing references:

```php
<?php

// Using parser
$parser->getEnvironment()->getErrorManager()->abortOnError(false);

// Using builder
$builder->getErrorManager()->abortOnError(false);
```

## Writing directives

### Step 1: Extends the Directive class

Write your own class that extends the `Gregwar\RST\Directive` class, and define the
method `getName()` that return the directive name.

You can then redefine one of the following method:

* `processAction()` if your directive simply tweak the document without modifying the nodes
* `processNode()` if your directive is adding a node
* `process()` if your directive is tweaking the node that just follows it

See `Directive.php` for more information

### Step 2: Register your directive

You can register your directive by directly calling `registerDirective()` on your
`Parser` object.

Else, you will have to also create your own kernel by extending the `Kernel` class
and adding your own logic to define extra directives, see `Kernel.php` for more information.
Then, pass the kernel when constructing the `Parser` or the `Builder`

## License

This library is under MIT license
