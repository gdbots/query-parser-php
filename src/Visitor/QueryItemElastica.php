<?php

namespace Gdbots\QueryParser\Visitor;

use Elastica\Query;
use Gdbots\QueryParser\Node;

class QueryItemElastica implements QueryItemVisitorInterface
{
    /**
     * @var string
     */
    protected $fieldName = 'title';

    /**
     * @param string $fieldName
     *
     * @return self
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        $query = new Query\Match();
        $query->setFieldQuery($this->fieldName, $word->getToken());

        if ($word->isBoosted()) {
            $query->setFieldBoost($this->fieldName, $word->getBoostBy());
        }

        return $this->convertToBoolQuery($word, $query);
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        $query = new Query\MatchPhrase();
        $query->setFieldQuery($this->fieldName, $phrase->getToken());

        if ($phrase->isBoosted()) {
            $query->setFieldBoost($this->fieldName, $phrase->getBoostBy());
        }

        return $this->convertToBoolQuery($phrase, $query);
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        $query = new Query\Term(['hashtag' => $hashtag->getToken()]);

        if ($hashtag->isBoosted()) {
            $query->setTerm($this->fieldName, $hashtag->getToken(), $hashtag->getBoostBy());
        }

        return $this->convertToBoolQuery($hashtag, $query);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        $query = new Query\Term(['mention' => $mention->getToken()]);

        if ($mention->isBoosted()) {
            $query->setTerm($this->fieldName, $mention->getToken(), $mention->getBoostBy());
        }

        return $this->convertToBoolQuery($mention, $query);
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

            $query = $operator == 'value'
                 ? new Query\Term([$term->getNominator()->getToken() => $term->getTerm()->getToken()])
                 : new Query\Range($term->getNominator()->getToken(), [$operator => $term->getTerm()->getToken()])
            ;

            if ($term->getTerm() instanceof Node\Range) {
                $range = json_decode($term->getTerm()->getToken(), true);

                $query = new Query\Range($term->getNominator()->getToken(), ['gte' => $range[0], 'lte' => $range[1]]);
            }

            if ($term->isBoosted()) {
                $query->addParam('boost', $term->getBoostBy());
            }

            return $this->convertToBoolQuery($term, $query);
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
        $boolQuery = new Query\Bool();

        foreach ($list->getExpressions() as $expression) {
            if ($query = $expression->accept($this)) {
                if ($query instanceof Query\Bool) {
                    if ($query->hasParam('should')) {
                        foreach ($query->getParam('should') as $q) {
                            $boolQuery->addShould($q);
                        }
                    }
                    if ($query->hasParam('must')) {
                        foreach ($query->getParam('must') as $q) {
                            $boolQuery->addMust($q);
                        }
                    }
                    if ($query->hasParam('must_not')) {
                        foreach ($query->getParam('must_not') as $q) {
                            $boolQuery->addMustNot($q);
                        }
                    }

                    continue;
                }

                $boolQuery->addShould($query);
            }
        }

        return $boolQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(Node\AndExpressionList $list)
    {
        $boolQuery = new Query\Bool();

        foreach ($list->getExpressions() as $expression) {
            if ($query = $expression->accept($this)) {
                if ($query instanceof Query\Bool) {
                    if ($query->hasParam('should')) {
                        foreach ($query->getParam('should') as $q) {
                            $boolQuery->addShould($q);
                        }
                    }
                    if ($query->hasParam('must')) {
                        foreach ($query->getParam('must') as $q) {
                            $boolQuery->addMust($q);
                        }
                    }
                    if ($query->hasParam('must_not')) {
                        foreach ($query->getParam('must_not') as $q) {
                            $boolQuery->addMustNot($q);
                        }
                    }

                    continue;
                }

                $boolQuery->addMust($query);
            }
        }

        return $boolQuery;
    }

    /**
     * Convert query object into BoolQuery if needed
     *
     * @param Node\AbstractQueryItem $term
     * @param Query\AbstractQuery    $query
     *
     * @return Query\AbstractQuery
     */
    protected function convertToBoolQuery(Node\AbstractQueryItem $term, Query\AbstractQuery $query)
    {
        if ($term->isExcluded()) {
            $boolQuery = new Query\Bool();
            $boolQuery->addMustNot($query);
            return $boolQuery;
        }

        if ($term->isIncluded()) {
            $boolQuery = new Query\Bool();
            $boolQuery->addMust($query);
            return $boolQuery;
        }

        return $query;
    }
}
