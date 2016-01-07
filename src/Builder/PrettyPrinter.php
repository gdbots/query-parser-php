<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Filter;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\ParsedQuery;

class PrettyPrinter implements QueryBuilder
{
    use QueryBuilderTrait;

    /** @var string */
    private $result;

    /**
     * @return string
     */
    public function getResult()
    {
        return trim($this->result);
    }

    /**
     * @param ParsedQuery $parsedQuery
     */
    protected function beforeFromParsedQuery(ParsedQuery $parsedQuery)
    {
        $this->result = '';
    }

    /**
     * @param Node $node
     */
    protected function printPrefix(Node $node)
    {
        if ($node->isRequired()) {
            $this->result .= '+';
        } elseif ($node->isProhibited()) {
            $this->result .= '-';
        }
    }

    /**
     * @param Node $node
     */
    protected function printPostfix(Node $node)
    {
        if ($node instanceof Word && $node->hasTrailingWildcard()) {
            $this->result .= '*';
        }

        if ($node->useBoost()) {
            $this->result .= '^'.$node->getBoost();
        } elseif ($node->useFuzzy()) {
            $this->result .= '~'.$node->getFuzzy();
        }

        if (!$this->inRange) {
            $this->result .= ' ';
        }
    }

    /**
     * @param Node $node
     */
    protected function handleTerm(Node $node)
    {
        $this->printPrefix($node);
        $this->result .= $node instanceof Phrase ? '"'.$node->getValue().'"' : $node->getValue();
        if ($this->inFilter && !$this->inRange && !$this->inSubquery) {
            return;
        }
        $this->printPostfix($node);
    }

    /**
     * @param Node $node
     */
    protected function handleExplicitTerm(Node $node)
    {
        $this->printPrefix($node);
        if ($node instanceof Hashtag) {
            $this->result .= '#';
        } elseif ($node instanceof Mention) {
            $this->result .= '@';
        }
        $this->result .= $node->getValue();
        if ($this->inFilter && !$this->inRange && !$this->inSubquery) {
            return;
        }
        $this->printPostfix($node);
    }

    /**
     * @param Node $node
     */
    protected function handleNumericTerm(Node $node)
    {
        $this->printPrefix($node);

        if ($node instanceof Number || $node instanceof Date) {
            switch ($node->getComparisonOperator()->getValue()) {
                case ComparisonOperator::GT:
                    $this->result .= '>';
                    break;

                case ComparisonOperator::GTE:
                    $this->result .= '>=';
                    break;

                case ComparisonOperator::LT:
                    $this->result .= '<';
                    break;

                case ComparisonOperator::LTE:
                    $this->result .= '<=';
                    break;

                default:
                    break;

            }
        }

        $this->result .= $node->getValue();
        if ($this->inFilter && !$this->inRange && !$this->inSubquery) {
            return;
        }
        $this->printPostfix($node);
    }

    /**
     * @param Filter $filter
     */
    protected function startFilter(Filter $filter)
    {
        $this->printPrefix($filter);
        $this->result .= $filter->getField().':';
    }

    /**
     * @param Filter $filter
     */
    protected function endFilter(Filter $filter)
    {
        $this->printPostfix($filter);
    }

    /**
     * @param Range $range
     */
    protected function startRange(Range $range)
    {
        $this->printPrefix($range);
        $this->result .= $range->isExclusive() ? '{' : '[';

        if ($range->hasLowerNode()) {
            $range->getLowerNode()->acceptBuilder($this);
        } else {
            $this->result .= '*';
        }

        $this->result .= '..';

        if ($range->hasUpperNode()) {
            $range->getUpperNode()->acceptBuilder($this);
        } else {
            $this->result .= '*';
        }
    }

    /**
     * @param Range $range
     */
    protected function endRange(Range $range)
    {
        $this->result .= $range->isExclusive() ? '}' : ']';
        $this->printPostfix($range);
    }

    /**
     * @param Subquery $subquery
     */
    protected function startSubquery(Subquery $subquery)
    {
        $this->printPrefix($subquery);
        $this->result .= '(';
    }

    /**
     * @param Subquery $subquery
     */
    protected function endSubquery(Subquery $subquery)
    {
        $this->result = trim($this->result).')';
        $this->printPostfix($subquery);
    }
}
