<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\NodeInterface;

/**
 * Represents an unary operator node, an unary operator can only have one child node
 */
abstract class AbstractUnary implements NodeInterface
{
    /**
     * The child node
     *
     * @var NodeInterface
     */
    protected $node = null;

    /**
     * The class constructor
     *
     * @param NodeInterface $node
     */
    public function __construct(NodeInterface $node)
    {
        $this->node = $node;
    }
}
