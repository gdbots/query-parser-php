<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class Negation extends CompositeExpression
{
    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'NOT',
            'Expression' => $this->expression
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitNegation($this);
    }
}
