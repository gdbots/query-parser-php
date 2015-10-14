<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\NodeInterface;

/**
 * Represents a binary operator node, a binary operator has a left and a right child node
 */
abstract class AbstractBinary implements NodeInterface
{
    /**
     * The left child node
     *
     * @var NodeInterface
     */
    protected $left = null;

    /**
     * The right child node
     *
     * @var NodeInterface
     */
    protected $right = null;

    /**
     * The class constructor
     *
     * @param NodeInterface $left
     * @param NodeInterface $right
     */
    public function __construct(NodeInterface $left, NodeInterface $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
}
