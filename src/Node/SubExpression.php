<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class SubExpression extends CompositeExpression
{
    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => '()',
            'Expression' => $this->expression
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitSubExpression($this);
    }
}
