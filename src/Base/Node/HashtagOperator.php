<?php

namespace Gdbots\QueryParser\Base\Node;

use Gdbots\QueryParser\Node\AbstractUnary;

/**
 * Represents a hashtag operator
 */
class HashtagOperator extends AbstractUnary
{
    /**
     * Evaluates the node
     *
     * @return string
     */
    public function evaluate()
    {
        $node = $this->node->evaluate();

        return '(#' . $node . ')';
    }
}
