<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;

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

    /**
     * @param int        $tokenType
     * @param QueryItem $queryItem
     *
     * @return array
     */
    public function getExpressions($tokenType = null, QueryItem $queryItem = null)
    {
        if ($tokenType) {
            $expressions = [];

            if (!$queryItem) {
                $queryItem = $this->getExpressions();
            }

            foreach ($queryItem as $expr) {
                if (method_exists($expr, 'getTokenType') && $expr->getTokenType() == $tokenType) {
                    $expressions[] = $expr;

                } elseif ($expr instanceof ExpressionList) {
                    $expressions = array_merge($expressions, $expr->getExpressions($tokenType));

                } elseif ($expr instanceof CompositeExpression) {
                    if (
                        ($expr instanceof Mention && $tokenType == QueryScanner::T_MENTION) ||
                        ($expr instanceof Hashtag && $tokenType == QueryScanner::T_HASHTAG) ||
                        ($expr instanceof ExcludeTerm && $tokenType == QueryScanner::T_EXCLUDE) ||
                        ($expr instanceof IncludeTerm && $tokenType == QueryScanner::T_INCLUDE)
                    ) {
                        $expressions[] = $expr;
                    }

                    $this->getExpressions($tokenType, $expr->getSubExpression());

                } elseif ($expr instanceof ExplicitTerm) {
                    if ($expr->getNominator()->getTokenType() == $tokenType) {
                        $expressions[] = $expr->getNominator();
                    }
                    if ($expr->getTerm()->getTokenType() == $tokenType) {
                        $expressions[] = $expr->getTerm();
                    }
                }

            }

            return $expressions;
        }

        return $this->expressions;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->expressions);
    }
}
