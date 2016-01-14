<?php

namespace Gdbots\QueryParser\Builder;

use Elastica\Filter;
use Elastica\Query;
use Elastica\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Word;

class ElasticaQueryBuilder extends AbstractQueryBuilder
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var Query\Bool */
    protected $boolQuery;

    /**
     * When a subquery is entered we'll take the current query
     * and save it here.  After the subquery completes we inject
     * the query back into the outer query.
     *
     * @var Query\Bool
     */
    protected $outerBoolQuery;

    /** @var Filter\Bool */
    protected $boolFilter;

    /** @var bool */
    protected $ignoreEmojis = true;

    /** @var bool */
    protected $ignoreEmoticons = true;

    /** @var bool */
    protected $ignoreStopWords = true;

    /** @var bool */
    protected $lowerCaseTerms = true;

    /**
     * ElasticaQueryBuilder constructor.
     */
    public function __construct()
    {
        $this->qb = new QueryBuilder();
        $this->clear();
    }

    /**
     * @return static
     */
    public function clear()
    {
        $this->boolQuery = $this->qb->query()->bool();
        $this->outerBoolQuery = $this->boolQuery;
        $this->boolFilter = $this->qb->filter()->bool();
        return $this;
    }

    /**
     * @param bool $ignoreEmojis
     * @return static
     */
    public function ignoreEmojis($ignoreEmojis = true)
    {
        $this->ignoreEmojis = (bool)$ignoreEmojis;
        return $this;
    }

    /**
     * @param bool $ignoreEmoticons
     * @return static
     */
    public function ignoreEmoticons($ignoreEmoticons = true)
    {
        $this->ignoreEmoticons = (bool)$ignoreEmoticons;
        return $this;
    }

    /**
     * @param bool $ignoreStopWords
     * @return static
     */
    public function ignoreStopWords($ignoreStopWords = true)
    {
        $this->ignoreStopWords = (bool)$ignoreStopWords;
        return $this;
    }

    /**
     * @param bool $lowerCaseTerms
     * @return static
     */
    public function lowerCaseTerms($lowerCaseTerms = true)
    {
        $this->lowerCaseTerms = (bool)$lowerCaseTerms;
        return $this;
    }

    /**
     * @return Query\Filtered
     */
    public function getFilteredQuery()
    {
        $this->boolQuery->setMinimumNumberShouldMatch('2<80%');
        return $this->qb->query()->filtered($this->boolQuery, $this->boolFilter);
    }

    /**
     * @param Range $range
     * @param Field $field
     * @param bool $cacheable
     */
    protected function handleRange(Range $range, Field $field, $cacheable = false)
    {
        $useBoost = $field->useBoost();
        $boost    = $field->getBoost();
        $boolOp   = $field->getBoolOperator();

        /*
         * Determine the method that will be used to add the range query
         * to the elastica query (bool or filter).  lucky for us, elastica
         * uses the same method names for both objects.
         */
        if ($boolOp->equals(BoolOperator::REQUIRED())) {
            $method = 'addMust';
        } elseif ($boolOp->equals(BoolOperator::PROHIBITED())) {
            $method = 'addMustNot';
        } else {
            $method = 'addShould';
        }

        if ($range->isExclusive()) {
            $lowerOperator = 'gt';
            $upperOperator = 'lt';
        } else {
            $lowerOperator = 'gte';
            $upperOperator = 'lte';
        }

        $data = [];

        if ($range instanceof DateRange) {
            if ($range->hasLowerNode()) {
                $data[$lowerOperator] = $range->getLowerNode()->toDateTime()->format('U');
            }
            if ($range->hasUpperNode()) {
                $data[$upperOperator] = $range->getUpperNode()->toDateTime()->modify('+1 day')->format('U');
            }
        } else {
            if ($range->hasLowerNode()) {
                $data[$lowerOperator] = $range->getLowerNode()->getValue();
            }
            if ($range->hasUpperNode()) {
                $data[$upperOperator] = $range->getUpperNode()->getValue();
            }
        }

        if ($cacheable) {
            $this->boolFilter->$method($this->qb->filter()->range($field->getName(), $data));
            return;
        }

        if ($useBoost) {
            $data['boost'] = $boost;
        }

        $this->boolQuery->$method($this->qb->query()->range($field->getName(), $data));
    }

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function startSubquery(Subquery $subquery, Field $field = null)
    {
        $this->outerBoolQuery = $this->boolQuery;
        $this->boolQuery = $this->qb->query()->bool();
    }

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function endSubquery(Subquery $subquery, Field $field = null)
    {
        $params = $this->boolQuery->getParams();
        if (!empty($params)) {
            $this->boolQuery->setMinimumNumberShouldMatch(1);

            if ($this->inField()) {
                $useBoost = $field->useBoost();
                $boost    = $field->getBoost();
                $boolOp   = $field->getBoolOperator();
            } else {
                $useBoost = $subquery->useBoost();
                $boost    = $subquery->getBoost();
                $boolOp   = $subquery->getBoolOperator();
            }

            if ($useBoost) {
                $this->boolQuery->setBoost($boost);
            }

            if ($boolOp->equals(BoolOperator::REQUIRED())) {
                $this->outerBoolQuery->addMust($this->boolQuery);
            } elseif ($boolOp->equals(BoolOperator::PROHIBITED())) {
                $this->outerBoolQuery->addMustNot($this->boolQuery);
            } else {
                $this->outerBoolQuery->addShould($this->boolQuery);
            }
        }

        $this->boolQuery = $this->outerBoolQuery;
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function mustMatch(Node $node, Field $field = null)
    {
        $this->addTextToQuery('addMust', $node, $field);
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function shouldMatch(Node $node, Field $field = null)
    {
        $this->addTextToQuery('addShould', $node, $field);
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function mustNotMatch(Node $node, Field $field = null)
    {
        $this->addTextToQuery('addMustNot', $node, $field);
    }

    /**
     * Adds a text node to the active query.  These all use the "match" when full
     * text searching is needed/supported.
     *
     * @param string $method
     * @param Node $node
     * @param Field|null $field
     */
    protected function addTextToQuery($method, Node $node, Field $field = null)
    {
        if ($node instanceof Word && $node->isStopWord() && $this->ignoreStopWords) {
            return;
        }

        $fieldName = $this->inField() ? $field->getName() : $this->defaultFieldName;

        if ($this->inField() && !$this->inSubquery()) {
            $useBoost  = $field->useBoost();
            $boost     = $field->getBoost();
            $useFuzzy  = $field->useFuzzy();
            $fuzzy     = $field->getFuzzy();
        } else {
            $useBoost  = $node->useBoost();
            $boost     = $node->getBoost();
            $useFuzzy  = $node->useFuzzy();
            $fuzzy     = $node->getFuzzy();
        }

        /*
         * Look for special chars and if found, enforce fuzzy.
         * todo: review this with more test cases
         */
        if (!$useFuzzy
            && $node instanceof Phrase
            && 'addShould' === $method
            && preg_match('/[^a-zA-Z0-9\s\._-]+/', $node->getValue())
        ) {
            $useFuzzy = true;
            $fuzzy = 1;
        }

        if ($useFuzzy && $node instanceof Phrase) {
            $data = [
                'query' => $node->getValue(),
                'type' => Phrase::NODE_TYPE,
                'lenient' => true,
                'phrase_slop' => $fuzzy,
            ];

            if ($useBoost) {
                $data['boost'] = $boost;
            }

            $query = $this->qb->query()->match();
            $query->setField($fieldName, $data);

        } elseif ($useFuzzy) {
            $query = $this->qb->query()->fuzzy();
            $query->setField($fieldName, $node->getValue());
            $query->setFieldOption('fuzziness', $fuzzy);

            if ($useBoost) {
                $query->setFieldOption('boost', $boost);
            }

        } elseif ($node instanceof Word && $node->hasTrailingWildcard()) {
            $query = $this->qb->query()->wildcard();
            $query->setValue($fieldName, strtolower($node->getValue()).'*', $useBoost ? $boost : Word::DEFAULT_BOOST);

        } else {
            $data = ['query' => $node->getValue(), 'operator' => 'and', 'lenient' => true];

            if ($useBoost) {
                $data['boost'] = $boost;
            }

            if ($node instanceof Phrase) {
                $data['type'] = Phrase::NODE_TYPE;
            }

            $query = $this->qb->query()->match();
            $query->setField($fieldName, $data);
        }

        $this->boolQuery->$method($query);
    }

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    protected function mustMatchTerm(Node $node, Field $field = null, $cacheable = false)
    {
        $this->addTermToQuery('addMust', $node, $field, $cacheable);
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function shouldMatchTerm(Node $node, Field $field = null)
    {
        $this->addTermToQuery('addShould', $node, $field);
    }

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    protected function mustNotMatchTerm(Node $node, Field $field = null, $cacheable = false)
    {
        $this->addTermToQuery('addMustNot', $node, $field, $cacheable);
    }

    /**
     * Adds a term to either the active query or filters.  Filters are used when
     * the request for that item could be cached, like documents with hashtag of cats.
     *
     * @param string $method
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    protected function addTermToQuery($method, Node $node, Field $field = null, $cacheable = false)
    {
        if ($node instanceof Emoji && $this->ignoreEmojis) {
            return;
        }

        if ($node instanceof Emoticon && $this->ignoreEmoticons) {
            return;
        }

        $value = $this->lowerCaseTerms ? strtolower($node->getValue()) : $node->getValue();
        $fieldName = $this->inField() ? $field->getName() : $this->defaultFieldName;

        if ($this->inField() && !$this->inSubquery()) {
            $useBoost = $field->useBoost();
            $boost    = $field->getBoost();
        } else {
            $useBoost = $node->useBoost();
            $boost    = $node->getBoost();
        }

        if ($node instanceof Date) {
            $term = $this->createDateRangeForSingleNode(
                $fieldName,
                $node,
                $cacheable,
                $useBoost ? $boost : Date::DEFAULT_BOOST
            );

        } elseif ($node instanceof Number && $node->useComparisonOperator()) {
            $data = [$node->getComparisonOperator()->getValue() => $value];
            if ($cacheable) {
                $term = $this->qb->filter()->range($fieldName, $data);
            } else {
                if ($useBoost) {
                    $data['boost'] = $boost;
                }
                $term = $this->qb->query()->range($fieldName, $data);
            }

        } else {
            if ($cacheable) {
                $term = $this->qb->filter()->term();
                $term->setTerm($fieldName, $value);
            } else {
                $term = $this->qb->query()->term();
                $term->setTerm($fieldName, $value, $boost);
            }
        }

        if ($cacheable) {
            $this->boolFilter->$method($term);
        } else {
            $this->boolQuery->$method($term);
        }
    }

    /**
     * When dealing with dates we have to create a range, even when the user provides
     * an exact date.  This is because a user asking for documents on date 2015-12-01
     * but the value is stored as a timestamp (for example).
     * So we ask for documents >=2015-12-01 and <=2015-12-02
     *
     * The Date node is always a UTC date with no time component. @see Date::toDateTime
     *
     * @param string $fieldName
     * @param Date $node
     * @param bool $cacheable
     * @param float $boost
     *
     * @return Filter\Range|Query\Range
     */
    protected function createDateRangeForSingleNode(
        $fieldName,
        Date $node,
        $cacheable = false,
        $boost = Date::DEFAULT_BOOST
    ) {
        $operator = $node->getComparisonOperator()->getValue();
        $date = $node->toDateTime();

        if ($operator === ComparisonOperator::EQ) {
            $data = ['gte' => $date->format('U'), 'lt' => $date->modify('+1 day')->format('U')];
        } else {
            $data = [$operator => $node->toDateTime()->format('U')];
        }

        if ($cacheable) {
            return $this->qb->filter()->range($fieldName, $data);
        }

        $data['boost'] = $boost;
        return $this->qb->query()->range($fieldName, $data);
    }
}
