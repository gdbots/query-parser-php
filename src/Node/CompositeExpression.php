<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;

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

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType)
    {
        $items = [];

        if (
            ($this instanceof Mention && $tokenType == QueryScanner::T_MENTION) ||
            ($this instanceof Hashtag && $tokenType == QueryScanner::T_HASHTAG) ||
            ($this instanceof ExcludeTerm && $tokenType == QueryScanner::T_EXCLUDE) ||
            ($this instanceof IncludeTerm && $tokenType == QueryScanner::T_INCLUDE)
        ) {
            $items[] = $this;
        }

        $items = array_merge($items, $this->getSubExpression()->getQueryItemsByTokenType($tokenType));

        return $items;
    }
}
