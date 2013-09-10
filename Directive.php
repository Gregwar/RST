<?php

namespace Gregwar\RST;

/**
 * A directive is like a function you can call or apply to a block
 * Il looks like:
 *
 * .. function:: main
 *     :arg1: value
 *     :arg2: otherValue
 *
 *     Some block !
 *
 *  The directive can define variables, create special nodes or change
 *  the node that directly follows it
 */
abstract class Directive
{
    /**
     * Get the directive name
     */
    abstract public function getName();

    /**
     * This is the function called by the parser to process the directive, it can be overloaded
     * to do anything with the document, like tweaking nodes or change the environment
     *
     * The node that directly follows the directive is also passed to it
     *
     * @param $parser the calling parser
     * @param $node the node that follows the directive
     * @param $variable the variable name of the directive
     * @param $data the data of the directive (following ::)
     * @param $ptions the array of options for this directive
     */
    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $document = $parser->getDocument();

        $processNode = $this->processNode($parser, $variable, $data, $options);

        if ($processNode) {
            if ($variable) {
                $environment = $parser->getEnvironment();
                $environment->setVariable($variable, $processNode);
            } else {
                $document->addNode($processNode);
            }
        }

        if ($node) {
            $document->addNode($node);
        }
    }

    /**
     * This can be overloaded to write a directive that just create one node for the
     * document, which is common
     *
     * The arguments are the same that process
     */
    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        $this->processAction($parser, $variable, $data, $options);

        return null;
    }

    /**
     * This can be overloaded to write a directive that just do an action without changing
     * the nodes of the document
     *
     * The arguments are the same that process
     */
    public function processAction(Parser $parser, $variabe, $data, array $options)
    {
    }

    /**
     * Called at the end of the parsing to finalize the document (add something or tweak nodes)
     */
    public function finalize(Document &$document)
    {
    }

    /**
     * Should the following block be passed as a CodeNode?
     */
    public function wantCode()
    {
        return false;
    }
}
