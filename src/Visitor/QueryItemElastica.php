<?php

namespace Gdbots\QueryParser\Visitor;

use Elastica\Filter\Query as FilterQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\Parser\QueryScanner;

class QueryItemElastica implements QueryItemVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        return new QueryString($word->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        return new QueryString($phrase->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function visitUrl(Node\Url $url)
    {
        return new QueryString($url->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        // return boost object
        if ($term->getTokenType() == QueryScanner::T_BOOST) {
            return $term->getNominator()->accept($this);;
        }

        if ($term->getNominator() instanceof Node\SimpleTerm) {
            $queryNominator = $term->getNominator()->accept($this);

        } else {
            $method = sprintf('visit%s', ucfirst(substr(get_class($term->getNominator()), 24)));
            if (method_exists($this, $method)) {
                $queryNominator = $this->$method($term->getNominator());
            }
        }

        $queryTerm = $term->getTerm()->accept($this);

        if ($term->hasParentTokenType(QueryScanner::T_BOOST)) {
            $queryTerm->setBoost($term->getParentTokenType(QueryScanner::T_BOOST));
        }

        return new Filtered($queryNominator, new FilterQuery($queryTerm));
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