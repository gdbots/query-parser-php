<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

final class DateRange extends Range
{
    const NODE_TYPE = 'date_range';
    const COMPOUND_NODE = true;

    public function __construct(?Date $lowerNode = null, ?Date $upperNode = null, bool $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Date|Node
     */
    public function getLowerNode(): ?Node
    {
        return parent::getLowerNode();
    }

    /**
     * @return Date|Node
     */
    public function getUpperNode(): ?Node
    {
        return parent::getUpperNode();
    }
}
