<?php

namespace Gdbots\QueryParser\Visitor;

use Elastica\Filter\Query as FilterQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\Parser\QueryScanner;

class QueryItemElastica implements QueryItemVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        $query = new QueryString($word->getToken());

        if ($word->hasParentTokenType(QueryScanner::T_BOOST)) {
            $query->setBoost($word->getParentTokenType(QueryScanner::T_BOOST));
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        $query = new QueryString($phrase->getToken());

        if ($phrase->hasParentTokenType(QueryScanner::T_BOOST)) {
            $query->setBoost($phrase->getParentTokenType(QueryScanner::T_BOOST));
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitUrl(Node\Url $url)
    {
        $query = new QueryString($url->getToken());

        if ($url->hasParentTokenType(QueryScanner::T_BOOST)) {
            $query->setBoost($url->getParentTokenType(QueryScanner::T_BOOST));
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\SimpleTerm) {
            $operator = 'value';

            switch ($term->getTokenTypeText()) {
                case ':>':
                    $operator = 'gte';
                    break;

                case ':<':
                    $operator = 'lt';
                    break;
            }

            $query = new Term([$term->getNominator()->getToken() => [$operator => $term->getTerm()->getToken()]]);

            if ($term->hasParentTokenType(QueryScanner::T_BOOST)) {
                $query->setParam('boost', $term->getParentTokenType(QueryScanner::T_BOOST));
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
    public function visitExcludeTerm(Node\ExcludeTerm $term)
    {
        $query = new BoolQuery();
        $query->addMustNot($term->getExpression()->accept($this));

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitIncludeTerm(Node\IncludeTerm $term)
    {
        $query = new BoolQuery();
        $query->addMust($term->getExpression()->accept($this));

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        // todo:
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        // todo:
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
