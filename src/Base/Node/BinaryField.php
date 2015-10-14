<?php

namespace Gdbots\QueryParser\Base\Node;

use Gdbots\QueryParser\Node\AbstractBinary;

/**
 * Represents a binary field
 */
class BinaryField extends AbstractBinary
{
    /**
     * Evaluates the node
     *
     * @return string
     */
    public function evaluate()
    {
        $left = $this->left->evaluate();
        $right = $this->right->evaluate();

        return '(' . $left . '=' . $right . ')';
    }
}
