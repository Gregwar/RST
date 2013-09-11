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
     * Resolve the reference and returns an array
     *
     * @param $environment the Environment in use
     * @param $data the data of the reference
     *
     * @return array an array with key title and url
     */
    abstract public function resolve(Environment $environment, $data);

    /**
     * Called when a reference is just found
     *
     * @param $environment the Environment in use
     * @param $data the data of the reference
     */
    public function found(Environment $environment, $data)
    {
    }
}
