<?php

namespace Gdbots\QueryParser;

/**
 * Represents an expression node
 */
interface NodeInterface
{
    /**
     * Evaluates the node
     */
    public function evaluate();
}
