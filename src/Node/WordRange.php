<?php

namespace Gdbots\QueryParser\Node;

final class WordRange extends Range
{
    const NODE_TYPE = 'word_range';
    const COMPOUND_NODE = true;

    /**
     * WordRange constructor.
     *
     * @param Word|null $lowerNode
     * @param Word|null $upperNode
     * @param bool $exclusive
     */
    public function __construct(Word $lowerNode = null, Word $upperNode = null, $exclusive = false)
    {
        parent::__construct($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return Word
     */
    public function getLowerNode()
    {
        return parent::getLowerNode();
    }

    /**
     * @return Word
     */
    public function getUpperNode()
    {
        return parent::getUpperNode();
    }
}
