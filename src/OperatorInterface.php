<?php

namespace Gdbots\QueryParser;

/**
 * Represents an operator
 */
interface OperatorInterface
{
    /**
     * Returns the precedence of the operator
     *
     * @return int
     */
    public function getPrecedence();

    /**
     * Returns the operator node instance for the operator
     *
     * @return Node
     */
    public function getNode();
}
