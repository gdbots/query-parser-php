<?php

namespace Gdbots\QueryParser\Visitor;

use Elastica\Filter\Query as FilterQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Elastica\Query\Range;
use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryLexer;

class QueryItemElastica implements QueryItemVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        $query = new QueryString($word->getToken());

        if ($word->isBoosted()) {
            $query->setBoost($word->getBoostBy());
        }

        if ($word->isExcluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMustNot($query);
            return $boolQuery;
        }

        if ($word->isIncluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust($query);
            return $boolQuery;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        $query = new QueryString($phrase->getToken());

        if ($phrase->isBoosted()) {
            $query->setBoost($phrase->getBoostBy());
        }

        if ($phrase->isExcluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMustNot($query);
            return $boolQuery;
        }

        if ($phrase->isIncluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust($query);
            return $boolQuery;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        // not yet implemented
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        // not yet implemented
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\AbstractSimpleTerm) {
            $operator = 'value';

            switch ($term->getTokenTypeText()) {
                case ':>':
                    $operator = 'gt';
                    break;

                case ':>=':
                    $operator = 'gte';
                    break;

                case ':<':
                    $operator = 'lt';
                    break;

                case ':<=':
                    $operator = 'lte';
                    break;
            }

            $query = new Term([$term->getNominator()->getToken() => [$operator => $term->getTerm()->getToken()]]);

            if ($term->getTerm() instanceof Node\Range) {
                $range = json_decode($term->getTerm()->getToken(), true);

                $query = new Range($term->getNominator()->getToken(), ['gte' => $range[0], 'lte' => $range[1]]);
            }

            if ($term->isBoosted()) {
                $query->addParam('boost', $term->getBoostBy());
            }

            if ($term->isExcluded()) {
                $boolQuery = new BoolQuery();
                $boolQuery->addMustNot($query);
                return $boolQuery;
            }

            if ($term->isIncluded()) {
                $boolQuery = new BoolQuery();
                $boolQuery->addMust($query);
                return $boolQuery;
            }

            return $query;
        }

        $method = sprintf('visit%s', ucfirst(substr(get_class($term->getNominator()), 24)));
        if (method_exists($this, $method)) {
            return $this->$method($term->getNominator());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(Node\SubExpression $sub)
    {
        return $sub->getExpression()->accept($this);
    }

    /**
     * {@inheritDoc}
     */
    public function visitOrExpressionList(Node\OrExpressionList $list)
    {
        $query = new BoolQuery();

        foreach ($list->getExpressions() as $expression) {
            if ($q = $expression->accept($this)) {
                $query->addShould($q);
            }
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(Node\AndExpressionList $list)
    {
        $query = new BoolQuery();

        foreach ($list->getExpressions() as $expression) {
            if ($q = $expression->accept($this)) {
                $query->addMust($q);
            }
        }

        return $query;
    }
}
