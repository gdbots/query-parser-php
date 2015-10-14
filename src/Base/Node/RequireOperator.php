<?php

namespace Gdbots\QueryParser\Base\Node;

use Gdbots\QueryParser\Node\AbstractUnary;

/**
 * Represents a require operator
 */
class RequireOperator extends AbstractUnary
{
    /**
     * Evaluates the node
     *
     * @return string
     */
    public function evaluate()
    {
        $node = $this->node->evaluate();

        return '(+' . $node . ')';
    }
}
