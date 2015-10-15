<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class OrExpressionList extends ExpressionList
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
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitOrExpressionList($this);
    }
}
