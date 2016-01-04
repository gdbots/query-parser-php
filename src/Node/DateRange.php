<?php

namespace Gdbots\QueryParser\Node;

final class DateRange extends Range
{
    const NODE_TYPE = 'date_range';

    /**
     * DateRange constructor.
     *
     * @param Date|null $lowerNode
     * @param Date|null $upperNode
     * @param bool $exclusive
     */
    public function __construct(Date $lowerNode = null, Date $upperNode = null, $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Date
     */
    public function getLowerNode()
    {
        return parent::getLowerNode();
    }

    /**
     * @return Date
     */
    public function getUpperNode()
    {
        return parent::getUpperNode();
    }
}
