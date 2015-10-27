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
                $items[] = $this;
            }
        } else {
            $items[QueryScanner::$typeStrings[$this->getTokenType()]][] = $this;
        }

        return $items;
    }
}
