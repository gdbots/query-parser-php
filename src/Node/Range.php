<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Range extends AbstractSimpleTerm
{
    /**
     * @param float $from
     * @param float $to
     */
    public function __construct($from, $to)
    {
        parent::__construct(QueryLexer::T_RANGE, json_encode([$from, $to]));
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
