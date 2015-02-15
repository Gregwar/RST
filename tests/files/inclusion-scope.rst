This first example will be parsed at the document level, and can
thus contain any construct, including section headers.

.. include:: inclusion-scope-include.rst

Back in the main document.

    This second example will be parsed in a block quote context.
    Therefore it may only contain body elements.  It may not
    contain section headers.

    .. include:: inclusion-scope-include.rst
