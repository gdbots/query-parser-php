<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class AndExpressionList extends ExpressionList
{
    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'AND',
            'Expressions' => $this->expressions
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitAndExpressionList($this);
    }
}
