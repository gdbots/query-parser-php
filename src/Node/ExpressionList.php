<?php

namespace Gdbots\QueryParser\Node;

abstract class ExpressionList extends QueryItem implements \Countable
{
    /**
     * @var array
     */
    protected $expressions;

    /**
     * @param array $expressions
     */
    public function __construct($expressions = array())
    {
        $this->expressions = $expressions;
    }

    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType)
    {
        $items = [];

        foreach ($this->getExpressions() as $expr) {
            if (method_exists($expr, 'getTokenType') && $expr->getTokenType() == $tokenType) {
                $items[] = $expr;

            } else {
                $items = array_merge($items, $expr->getQueryItemsByTokenType($tokenType));
            }
        }

        return $items;
    }
}
