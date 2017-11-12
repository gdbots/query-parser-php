<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

final class NumberRange extends Range
{
    const NODE_TYPE = 'number_range';
    const COMPOUND_NODE = true;

    /**
     * NumberRange constructor.
     *
     * @param Numbr $lowerNode
     * @param Numbr $upperNode
     * @param bool  $exclusive
     */
    public function __construct(?Numbr $lowerNode = null, ?Numbr $upperNode = null, bool $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Numbr|Node
     */
    public function getLowerNode(): ?Node
    {
        return parent::getLowerNode();
    }

    /**
     * @return Numbr|Node
     */
    public function getUpperNode(): ?Node
    {
        return parent::getUpperNode();
    }
}
