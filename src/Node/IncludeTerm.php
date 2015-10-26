<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class IncludeTerm extends CompositeExpression
{
    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'Include Term',
            'Expression' => $this->expression
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitIncludeTerm($this);
    }
}
