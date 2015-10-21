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
    public function getQueryItemsByTokenType($tokenType = null)
    {
        $items = [];

        if ($tokenType) {
            if (
                ($this instanceof Mention && $tokenType == QueryScanner::T_MENTION) ||
                ($this instanceof Hashtag && $tokenType == QueryScanner::T_HASHTAG) ||
                ($this instanceof ExcludeTerm && $tokenType == QueryScanner::T_EXCLUDE) ||
                ($this instanceof IncludeTerm && $tokenType == QueryScanner::T_INCLUDE)
            ) {
                $items[] = $this;
            }
        } else {
            if ($this instanceof Mention) {
                $items[QueryScanner::$typeStrings[QueryScanner::T_MENTION]][] = $this;
            }
            if ($this instanceof Hashtag) {
                $items[QueryScanner::$typeStrings[QueryScanner::T_HASHTAG]][] = $this;
            }
            if ($this instanceof ExcludeTerm) {
                $items[QueryScanner::$typeStrings[QueryScanner::T_EXCLUDE]][] = $this;
            }
            if ($this instanceof IncludeTerm) {
                $items[QueryScanner::$typeStrings[QueryScanner::T_INCLUDE]][] = $this;
            }
        }

        if (!($this->getExpression() instanceof SimpleTerm)) {
            $items = array_merge_recursive($items, $this->getExpression()->getQueryItemsByTokenType($tokenType));
        }

        return $items;
    }
}
