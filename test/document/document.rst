.. document::
.. title:: Demo document
.. stylesheet:: style.css
.. meta:: 
    :description: A demo Gregwar/RST document

.. .. this is a comment!

Gregwar/RST Sandbox
===================

Reference to :doc:`HoHo <some/doc>`

.. note:: 
    **Note**: 
    This is a testing sandbox, if you want to understand how it works, have a
    look to the rst original file

#=) Titles
----------

#-) Using titles
~~~~~~~~~~~~~~~~

Titles can be wrote using the underlining, the default order is:

1. ``=======``
2. ``-------``
3. ``~~~~~~~``
4. ``*******``

#-) Using auto-numbering
~~~~~~~~~~~~~~~~~~~~~~~~

This is not a standard of RST but is really useful, you can use the special syntax
``#`` followed by the letter of the title you are in (it resets the counter when used).

You can for instance use ``Question #*`` if you are under an ``*******``, the number
displayed will be auto-incremented:

** Question #* **

The first question

** Question #* **

The second question

This can of course also be used to number parts, chapter etc.


#-) Separator
~~~~~~~~~~~~~

A separator is like a title underline but without any text above::

    -----

This will result in a text separation:

----

#=) Inline style
----------------

* ``*italic*`` renders as *italic*
* ``**strong**`` renders as **strong**

You can force a line break by adding an extra space at the end of a line

#=) Tables
----------

Tables can be created using the line separator ``====``::

    ================     ================
    **First column**     **Other column**
    ================     ================
    Second row with      Second row, of the
    some contents text   other column
    ============         ================

Will result in:

================     ================
**First column**     **Other column**
================     ================
Second row with      Second row, of the
some contents text   other column
============         ================

Another example:

===    ===   ===
Col A  Col B Col C
===    ===   ===
Col X  Col Y Col Z
===    ===   ===
Col U  Col J Col K
===    ===   ===

#=) Lists
---------

Lists can be ordered or unordered, and nested, for instance this::

    * Element A
        * Sub A, this a
          multiline sentence in the source
            1. Sub ordered as "1"
            2. Sub ordered as "2"
        * Sub hello two
    * Element B

While result in:

* Element A
    * Sub A, this a
      multiline sentence in the source
        1. Sub ordered as "1"
        2. Sub ordered as "2"
    * Sub hello two
* Element B

#=) Blocks
----------

#-) Quoting
~~~~~~~~~~~

You can quote a block by indenting it::

    This is a normal pagagraph

        This is a quote

Will result in:

This is a normal paragraph

    This is a quote

#-) Code
~~~~~~~~

You can quote code the same way as quote, but using the ``::`` at the end
of the previous paragraph::

    Here is a piece of code:

    .. code-block:: php

        <?php

        echo "I love RST";

Will result in:

Here is a piece of code:

.. code-block:: php

    <?php

    echo "I love RST";

#=) Links
---------

#-) Standard links
~~~~~~~~~~~~~~~~~~

Links can be defined once for all using the trailing ``_``, like this::

    PHP_ is a great language

    .. _PHP: http://php.net/

Will result in:
    
PHP_ is a great language

.. _PHP:   http://php.net/   

#-) Anonymous links
~~~~~~~~~~~~~~~~~~~

Anonymous links can also be used to avoid copying the name just after the
block that uses it, for instance::

    I love GitHub__

    .. __: http://www.github.com/

Will result in:

I love GitHub__

.. __: http://www.github.com/

You can use the following shortcut::

    I love GitHub__

    __ http://www.github.com/
    
#-) Inline links
~~~~~~~~~~~~~~~~

You can also define the link target inside the link::

    Do you know `Google <http://www.google.com>`_ ?

Will result in:

Do you know `Google <http://www.google.com>`_ ?

#-) Anchor links
~~~~~~~~~~~~~~~~

An anchor can be used like this::

    .. _anchor:

    Some anchor section, you can link to it like `this <#anchor>`_

Will result in:

.. _anchor:

Some anchor section, you can link to it like `this <#anchor>`_

#=) Directives
--------------

#-) Include
~~~~~~~~~~~
    
.. include:: include.rst

#-) Replace
~~~~~~~~~~~

You can use the replace directive like this::

    .. |name| replace:: bob

    Hello |name| !

Will result in:
    
.. |name| replace:: bob

Hello |name| !

#-) Image
~~~~~~~~~

The ``image`` directive can be used to display images, this way::

    .. image:: rst.png
        :width: 250px
        :title: RST logo

Will result in:

.. image:: rst.png
    :width: 250px
    :title: RST logo
