<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Range extends AbstractSimpleTerm
{
    /**
     * @param float $start
     * @param float $end
     */
    public function __construct($start, $end)
    {
        parent::__construct(QueryLexer::T_RANGE, json_encode([$start, $end]));
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Expression' => 'Range',
            'Term' => $this->getToken()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return null;
    }
}
