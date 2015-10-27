<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryScanner;

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

        if (in_array($this->getTokenType(), [QueryScanner::T_EXCLUDE, QueryScanner::T_INCLUDE])) {
            $this->expression->addParentTokenType($this->getTokenType());
        }
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
        if ($this instanceof Mention) {
            return QueryScanner::T_MENTION;
        }
        if ($this instanceof Hashtag) {
            return QueryScanner::T_HASHTAG;
        }
        if ($this instanceof ExcludeTerm) {
            return QueryScanner::T_EXCLUDE;
        }
        if ($this instanceof IncludeTerm) {
            return QueryScanner::T_INCLUDE;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        if (!$this->getTokenType()) {
            return [];
        }

        $items = [];

        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
                if (in_array($this->getTokenType(), [QueryScanner::T_EXCLUDE, QueryScanner::T_INCLUDE])) {
                    $items = array_merge_recursive($items,
                        $this->getExpression()->getQueryItemsByTokenType($this->getExpression()->getTokenType())
                    );
                } else {
                    $items[] = $this;
                }
            } elseif (in_array($this->getTokenType(), [QueryScanner::T_EXCLUDE, QueryScanner::T_INCLUDE])) {
                $items = array_merge_recursive($items, $this->getExpression()->getQueryItemsByTokenType($tokenType));
            }
        } else {
            if (in_array($this->getTokenType(), [QueryScanner::T_EXCLUDE, QueryScanner::T_INCLUDE])) {
                $items = array_merge_recursive($items, $this->getExpression()->getQueryItemsByTokenType());
            } else {
                $items[QueryScanner::$typeStrings[$this->getTokenType()]][] = $this;
            }
        }

        return $items;
    }
}
