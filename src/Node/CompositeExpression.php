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
    public function getExpression()
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

        if (!($this->getExpression() instanceof SimpleTerm)) {
            $items = array_merge($items, $this->getExpression()->getQueryItemsByTokenType($tokenType));
        }

        return $items;
    }
}
