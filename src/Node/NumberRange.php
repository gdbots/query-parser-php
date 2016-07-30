<?php

namespace Gdbots\QueryParser\Node;

final class NumberRange extends Range
{
    const NODE_TYPE = 'number_range';
    const COMPOUND_NODE = true;

    /**
     * NumberRange constructor.
     *
     * @param Numbr|null $lowerNode
     * @param Numbr|null $upperNode
     * @param bool $exclusive
     */
    public function __construct(Numbr $lowerNode = null, Numbr $upperNode = null, $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Numbr
     */
    public function getLowerNode()
    {
        return parent::getLowerNode();
    }

    /**
     * @return Numbr
     */
    public function getUpperNode()
    {
        return parent::getUpperNode();
    }
}
