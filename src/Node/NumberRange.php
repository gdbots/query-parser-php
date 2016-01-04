<?php

namespace Gdbots\QueryParser\Node;

final class NumberRange extends Range
{
    const NODE_TYPE = 'number_range';

    /**
     * NumberRange constructor.
     *
     * @param \Gdbots\QueryParser\Node\Number|null $lowerNode
     * @param \Gdbots\QueryParser\Node\Number|null $upperNode
     * @param bool $exclusive
     */
    public function __construct(Number $lowerNode = null, Number $upperNode = null, $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Number
     */
    public function getLowerNode()
    {
        return parent::getLowerNode();
    }

    /**
     * @return Number
     */
    public function getUpperNode()
    {
        return parent::getUpperNode();
    }
}
