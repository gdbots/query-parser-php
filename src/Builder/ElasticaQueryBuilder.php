<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Builder;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Nested;
use Elastica\Query\Range as RangeQuery;
use Elastica\QueryBuilder as RuflinQueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Numbr;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Word;

class ElasticaQueryBuilder extends AbstractQueryBuilder
{
    /** @var RuflinQueryBuilder */
    protected $qb;

    /** @var BoolQuery */
    protected $boolQuery;

    /**
     * When a subquery is entered we'll take the current query
     * and save it here.  After the subquery completes we inject
     * the query back into the outer query.
     *
     * @var BoolQuery
     */
    protected $outerBoolQuery;

    /** @var bool */
    protected $ignoreEmojis = true;

    /** @var bool */
    protected $ignoreEmoticons = true;

    /** @var bool */
    protected $ignoreStopWords = true;

    /** @var bool */
    protected $lowerCaseTerms = true;

    /**
     * Array of field names which are nested objects in ElasticSearch and
     * must be queried using a nested query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/nested.html
     *
     * @var string[]
     */
    protected $nestedFields = [];

    /**
     * Any fields encountered that are nested are stored as a nested query
     * keyed by the nested field path and query method. e.g. "comments-addMust"
     *
     * The nested query contains a bool query and works exactly like the bool
     * query non-nested queries are added to.
     *
     * @var Nested[]
     */
    protected $nestedQueries = [];

    /**
     * ElasticaQueryBuilder constructor.
     */
    public function __construct()
    {
        $this->qb = new RuflinQueryBuilder();
        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): QueryBuilder
    {
        $this->boolQuery = $this->qb->query()->bool();
        $this->outerBoolQuery = $this->boolQuery;
        $this->nestedQueries = [];
        return $this;
    }

    /**
     * @param bool $ignoreEmojis
     *
     * @return static
     */
    public function ignoreEmojis(bool $ignoreEmojis = true): self
    {
        $this->ignoreEmojis = $ignoreEmojis;
        return $this;
    }

    /**
     * @param bool $ignoreEmoticons
     *
     * @return static
     */
    public function ignoreEmoticons(bool $ignoreEmoticons = true): self
    {
        $this->ignoreEmoticons = $ignoreEmoticons;
        return $this;
    }

    /**
     * @param bool $ignoreStopWords
     *
     * @return static
     */
    public function ignoreStopWords(bool $ignoreStopWords = true): self
    {
        $this->ignoreStopWords = $ignoreStopWords;
        return $this;
    }

    /**
     * @param bool $lowerCaseTerms
     *
     * @return static
     */
    public function lowerCaseTerms(bool $lowerCaseTerms = true): self
    {
        $this->lowerCaseTerms = $lowerCaseTerms;
        return $this;
    }

    /**
     * @param string[] $fields
     *
     * @return static
     */
    public function setNestedFields(array $fields): self
    {
        $this->nestedFields = array_flip($fields);
        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function addNestedField(string $fieldName): self
    {
        $this->nestedFields[$fieldName] = true;
        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function removeNestedField(string $fieldName): self
    {
        unset($this->nestedFields[$fieldName]);
        return $this;
    }

    /**
     * @return array
     */
    public function getNestedFields(): array
    {
        return array_keys($this->nestedFields);
    }

    /**
     * @return BoolQuery
     */
    public function getBoolQuery(): BoolQuery
    {
        if ($this->boolQuery->hasParam('must')) {
            // if a "must" is used we assume they wanted everything else optional
            return $this->boolQuery;
        }

        return $this->boolQuery->setMinimumShouldMatch('2<80%');
    }

    /**
     * {@inheritdoc}
     */
    protected function handleRange(Range $range, Field $field, bool $cacheable = false): void
    {
        $useBoost = $field->useBoost();
        $boost = $field->getBoost();
        $boolOp = $field->getBoolOperator();

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
                $data[$lowerOperator] = $range->getLowerNode()
                    ->toDateTime($this->localTimeZone)
                    ->format('Y-m-d');
            }
            if ($range->hasUpperNode()) {
                $data[$upperOperator] = $range->getUpperNode()
                    ->toDateTime($this->localTimeZone)
                    ->modify('+1 day')
                    ->format('Y-m-d');
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
            if ('addMustNot' === $method) {
                $this->addToBoolQuery($method, $field->getName(), $this->qb->query()->range($field->getName(), $data));
            } else {
                $this->addToBoolQuery('addFilter', $field->getName(), $this->qb->query()->range($field->getName(), $data));
            }

            return;
        }

        if ($useBoost) {
            $data['boost'] = $boost;
        }

        $this->addToBoolQuery($method, $field->getName(), $this->qb->query()->range($field->getName(), $data));
    }

    /**
     * {@inheritdoc}
     */
    protected function startSubquery(Subquery $subquery, ?Field $field = null): void
    {
        $this->outerBoolQuery = $this->boolQuery;
        $this->boolQuery = $this->qb->query()->bool();
    }

    /**
     * {@inheritdoc}
     */
    protected function endSubquery(Subquery $subquery, ?Field $field = null): void
    {
        $params = $this->boolQuery->getParams();
        if (!empty($params)) {
            $this->boolQuery->setMinimumShouldMatch(1);

            if ($this->inField()) {
                $useBoost = $field->useBoost();
                $boost = $field->getBoost();
                $boolOp = $field->getBoolOperator();
            } else {
                $useBoost = $subquery->useBoost();
                $boost = $subquery->getBoost();
                $boolOp = $subquery->getBoolOperator();
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
     * {@inheritdoc}
     */
    protected function mustMatch(Node $node, ?Field $field = null): void
    {
        $this->addTextToQuery('addMust', $node, $field);
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldMatch(Node $node, ?Field $field = null): void
    {
        $this->addTextToQuery('addShould', $node, $field);
    }

    /**
     * {@inheritdoc}
     */
    protected function mustNotMatch(Node $node, ?Field $field = null): void
    {
        $this->addTextToQuery('addMustNot', $node, $field);
    }

    /**
     * Adds a text node to the active query.  These all use the "match" when full
     * text searching is needed/supported.
     *
     * @param string $method
     * @param Node   $node
     * @param Field  $field
     */
    protected function addTextToQuery(string $method, Node $node, ?Field $field = null): void
    {
        if ($node instanceof Word && $node->isStopWord() && $this->ignoreStopWords) {
            return;
        }

        $fieldName = $this->inField() ? $field->getName() : $this->defaultFieldName;

        if ($this->inField() && !$this->inSubquery()) {
            $useBoost = $field->useBoost();
            $boost = $field->getBoost();
            $useFuzzy = $field->useFuzzy();
            $fuzzy = $field->getFuzzy();
        } else {
            $useBoost = $node->useBoost();
            $boost = $node->getBoost();
            $useFuzzy = $node->useFuzzy();
            $fuzzy = $node->getFuzzy();
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
                'query'       => $node->getValue(),
                'type'        => Phrase::NODE_TYPE,
                'lenient'     => true,
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
            $query->setValue($fieldName, strtolower($node->getValue()) . '*', $useBoost ? $boost : Word::DEFAULT_BOOST);
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

        $this->addToBoolQuery($method, $fieldName, $query);
    }

    /**
     * {@inheritdoc}
     */
    protected function mustMatchTerm(Node $node, ?Field $field = null, bool $cacheable = false): void
    {
        $this->addTermToQuery('addMust', $node, $field, $cacheable);
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldMatchTerm(Node $node, ?Field $field = null): void
    {
        $this->addTermToQuery('addShould', $node, $field);
    }

    /**
     * {@inheritdoc}
     */
    protected function mustNotMatchTerm(Node $node, ?Field $field = null, bool $cacheable = false): void
    {
        $this->addTermToQuery('addMustNot', $node, $field, $cacheable);
    }

    /**
     * Adds a term to the bool query or filter context.  Filter context is used when the
     * request for that item could be cached, like documents with hashtag of cats.
     *
     * @param string $method
     * @param Node   $node
     * @param Field  $field
     * @param bool   $cacheable
     */
    protected function addTermToQuery(string $method, Node $node, ?Field $field = null, bool $cacheable = false): void
    {
        if ($node instanceof Emoji && $this->ignoreEmojis) {
            return;
        }

        if ($node instanceof Emoticon && $this->ignoreEmoticons) {
            return;
        }

        $value = $this->lowerCaseTerms &&  is_string($node->getValue()) ? strtolower((string)$node->getValue()) : $node->getValue();
        $fieldName = $this->inField() ? $field->getName() : $this->defaultFieldName;

        if ($this->inField() && !$this->inSubquery()) {
            $useBoost = $field->useBoost();
            $boost = $field->getBoost();
        } else {
            $useBoost = $node->useBoost();
            $boost = $node->getBoost();
        }

        if ('_exists_' === $fieldName) {
            $term = new Exists($value);
            $method = 'addMust';
            $cacheable = true;
        } elseif ('_missing_' === $fieldName) {
            $term = new Exists($value);
            $method = 'addMustNot';
            $cacheable = true;
        } elseif ($node instanceof Date) {
            $term = $this->createDateRangeForSingleNode(
                $fieldName,
                $node,
                $cacheable,
                $useBoost ? $boost : Date::DEFAULT_BOOST
            );
        } elseif ($node instanceof Numbr && $node->useComparisonOperator()) {
            $data = [$node->getComparisonOperator()->getValue() => $value];
            if ($useBoost) {
                $data['boost'] = $boost;
            }
            $term = $this->qb->query()->range($fieldName, $data);
        } else {
            $term = $this->qb->query()->term();
            $term->setTerm($fieldName, $value, $boost);
        }

        if ($cacheable) {
            if ('addMustNot' === $method) {
                $this->addToBoolQuery($method, $fieldName, $term);
            } else {
                $this->addToBoolQuery('addFilter', $fieldName, $term);
            }
        } else {
            $this->addToBoolQuery($method, $fieldName, $term);
        }
    }

    /**
     * When dealing with dates we have to create a range, even when the user provides
     * an exact date.  This is because a user asking for documents on date 2015-12-01
     * but the value is stored as a timestamp (for example).
     * So we ask for documents >=2015-12-01 and <=2015-12-02
     *
     * The Date node is a date with no time component. @see Date::toDateTime
     *
     * @param string $fieldName
     * @param Date   $node
     * @param bool   $cacheable
     * @param float  $boost
     *
     * @return RangeQuery
     */
    protected function createDateRangeForSingleNode(
        string $fieldName,
        Date $node,
        bool $cacheable = false,
        float $boost = Date::DEFAULT_BOOST
    ): RangeQuery {
        $operator = $node->getComparisonOperator()->getValue();

        if ($operator === ComparisonOperator::EQ) {
            $date = $node->toDateTime($this->localTimeZone);
            $data = [
                'gte' => $date->format('Y-m-d'),
                'lt'  => $date->modify('+1 day')->format('Y-m-d'),
            ];
        } else {
            $data = [$operator => $node->toDateTime($this->localTimeZone)->format('Y-m-d')];
        }

        if ($cacheable) {
            return $this->qb->query()->range($fieldName, $data);
        }

        $data['boost'] = $boost;
        return $this->qb->query()->range($fieldName, $data);
    }

    /**
     * @param string        $method
     * @param string        $fieldName
     * @param AbstractQuery $query
     */
    protected function addToBoolQuery(string $method, string $fieldName, AbstractQuery $query): void
    {
        if (false === strpos($fieldName, '.')) {
            $this->boolQuery->$method($query);
            return;
        }

        $fieldName = str_replace('.raw', '', $fieldName);
        $nestedPath = substr($fieldName, 0, strrpos($fieldName, '.'));
        if (!isset($this->nestedFields[$nestedPath])) {
            $this->boolQuery->$method($query);
            return;
        }

        $nestedQuery = $nestedPath . '-' . $method;
        if (!isset($this->nestedQueries[$nestedQuery])) {
            $this->nestedQueries[$nestedQuery] = (new Nested())
                ->setQuery($this->qb->query()->bool()->setMinimumShouldMatch('2<80%'))
                ->setPath($nestedPath);
            $this->boolQuery->$method($this->nestedQueries[$nestedQuery]);
        }

        $this->nestedQueries[$nestedQuery]->getParam('query')->$method($query);
    }
}
