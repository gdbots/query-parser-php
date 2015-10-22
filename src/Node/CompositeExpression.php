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
        $this->expression->addParentTokenType($this->getTokenType());
    }

    /**
     * @return QueryItem
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return int
     */
    public function getTokenType()
    {
        if ($this instanceof Mention)     return QueryScanner::T_MENTION;
        if ($this instanceof Hashtag)     return QueryScanner::T_HASHTAG;
        if ($this instanceof ExcludeTerm) return QueryScanner::T_EXCLUDE;
        if ($this instanceof IncludeTerm) return QueryScanner::T_INCLUDE;
        if ($this instanceof IncludeTerm) return QueryScanner::T_INCLUDE;

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        $items = [];

        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
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
