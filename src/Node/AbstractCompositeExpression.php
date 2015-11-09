<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;

abstract class AbstractCompositeExpression extends AbstractQueryItem
{
    /**
     * @var AbstractQueryItem
     */
    protected $expression;

    /**
     * @param AbstractQueryItem $expression
     */
    public function __construct(AbstractQueryItem $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return AbstractQueryItem
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

        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
                return [$this];
            }

            return [];
        }

        return [QueryLexer::$typeStrings[$this->getTokenType()] => [$this]];
    }
}
