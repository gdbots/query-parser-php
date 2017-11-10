<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

final class WordRange extends Range
{
    const NODE_TYPE = 'word_range';
    const COMPOUND_NODE = true;

    /**
     * WordRange constructor.
     *
     * @param Word $lowerNode
     * @param Word $upperNode
     * @param bool $exclusive
     */
    public function __construct(?Word $lowerNode = null, ?Word $upperNode = null, bool $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Word|Node
     */
    public function getLowerNode(): ?Node
    {
        return parent::getLowerNode();
    }

    /**
     * @return Word|Node
     */
    public function getUpperNode(): ?Node
    {
        return parent::getUpperNode();
    }
}
