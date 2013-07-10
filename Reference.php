<?php

namespace Gregwar\RST;

/**
 * A reference is something that can be resolved in the document, for instance:
 *
 * :method:`helloWorld()`
 *
 * Will be resolved as a reference of type method and the given reference will
 * be called to resolve it
 */
abstract class Reference
{
    /**
     * The name of the reference, i.e the :something:
     */
    abstract public function getName();

    /**
     * Resolve the reference and returns a link
     */
    abstract public function resolve(Environment $environment, $data);

    /**
     * Called when a reference is just found
     */
    public function found(Environment $environment, $data)
    {
    }
}
