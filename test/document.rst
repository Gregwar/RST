Gregwar/RST Cheat Sheet
=======================

*Note: this is my developping sandbox, will be documented more clearly later*

Title
-----

.. This is a comment and won't appear in the final render

The second-level titles are specified with ``---`` under the text

Go to the hyperlinks_ section

Using the auto-numbering
------------------------

The third-level titles are specified with ``~~~`` under the text

Using auto-numbering
********************

This is not a standard of RST but is really useful, you can use the special syntax
``#`` followed by the letter of the title you are in (it resets the counter when used).

You can for instance use ``Question #*`` if you are under an ``*******``, the number
displayed will be auto-incremented:

** Question #* **

The first question

** Question #* **

The second question


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

* ``*italic*`` renders as *italic*
* ``**strong**`` renders as **strong**

Directives
----------

.. |test| replace:: The Test String!!
    :opt: 123
.. |othertest| replace:: An other test!

Testing the replace: |test|, an other: |othertest|

.. |testing| replace:: Magic
Testing

.. _hyperlinks:

Hyperlinks
----------

Do you want to learn about PHP_, this is `my favorite language`_

.. _PHP: http://php.net/
.. _my favorite language: http://php.net/

Do you know `Annymous links`__ ?

.. __: http://anon.ymo.us/

It's great, and can be defined quickly__

__ http://quickly.anonymous.com/

Code block
----------

.. code-block:: test

    This is a multiple line block

    Of code!!

Image
-----

.. image:: https://www.google.com/images/srpr/logo4w.png
    :width: 250px
    :title: The Google logo

