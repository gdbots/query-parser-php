<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class Mention extends CompositeExpression
{
    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'MENTION',
            'Expression' => $this->expression
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitMention($this);
    }
}
