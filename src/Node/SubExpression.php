<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class SubExpression extends AbstractCompositeExpression
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
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitSubExpression($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        if ($this->getExpression()) {
            return $this->getExpression()->getQueryItemsByTokenType($tokenType);
        }

        return [];
    }
}
