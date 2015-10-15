<?php

namespace Gdbots\QueryParser\Node;

abstract class CompositeExpression extends QueryItem
{
    /**
     * @var QueryItem
     */
    protected $expression;

    /**
     * @param QueryItem $expression
     */
    public function __construct(QueryItem $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return QueryItem
     */
    public function getSubExpression()
    {
        return $this->expression;
    }
}
