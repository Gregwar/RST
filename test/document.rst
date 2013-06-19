Gregwar/RST Cheat Sheet
=======================

Title
-----

The second-level titles are specified with `---` under the text

Sub title
~~~~~~~~~

The third-level titles are specified with `~~~` under the text

Blocks
------

Separator
~~~~~~~~~

A separator is like a title underline but without any text :

-----

This will result in a text separation

Lists
~~~~~

Test list :

* Element A
    * Sub A, this a
      multiline sentence in the source
        1. Sub ordered as "1"
        2. Sub ordered as "2"
    * Sub hello two
* Element B

Quote
-----

As Shakespeare said:

    To thine own self be true, and it must follow, as the night the day, thou canst not then be false to any man.

    And

    God has given you one face, and you make yourself another.

Code
----

Here is a piece of code::

    <?php

    echo "I love RST";

Inline style
------------

* `*italic*` renders as *italic*
* `**strong**` renders as **strong**
* `_underlined_` renders as _underlined_

Directives
----------

.. test::
    :a: 123

