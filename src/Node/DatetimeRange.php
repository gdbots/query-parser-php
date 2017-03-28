<?php

namespace Gdbots\QueryParser\Node;

final class DatetimeRange extends Range
{
    const NODE_TYPE = 'datetime_range';
    const COMPOUND_NODE = true;

    /**
     * DatetimeRange constructor.
     *
     * @param Datetime|null $lowerNode
     * @param Datetime|null $upperNode
     * @param bool $exclusive
     */
    public function __construct(Datetime $lowerNode = null, Datetime $upperNode = null, $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Datetime
     */
    public function getLowerNode()
    {
        return parent::getLowerNode();
    }

    /**
     * @return Datetime
     */
    public function getUpperNode()
    {
        return parent::getUpperNode();
    }
}