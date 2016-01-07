<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;

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

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder)
    {
        $builder->addDateRange($this);
    }
}
