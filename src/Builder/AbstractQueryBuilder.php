<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Enum\FieldType;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\NumberRange;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\WordRange;
use Gdbots\QueryParser\ParsedQuery;

abstract class AbstractQueryBuilder implements QueryBuilder
{
    /** @var ParsedQuery */
    protected $parsedQuery;

    /** @var Field */
    protected $currentField;

    /** @var bool */
    protected $inField = false;

    /** @var bool */
    protected $inRange = false;

    /** @var bool */
    protected $inSubquery = false;

    /**
     * Array of field names which support full text queries.  This value is
     * just a default set of common full text fields.
     *
     * @var array
     */
    private $fullTextSearchFields = [
        '_all' => true,
        'title' => true,
        'tiny_title' => true,
        'short_title' => true,
        'excerpt' => true,
        'description' => true,
        'abstract' => true,
        'search_text' => true,
        'bio' => true,
        'mini_bio' => true,
        'seo_title' => true,
        'seo_keywords' => true,
        'img_credit' => true,
        'img_caption' => true,
        'credit' => true,
        'caption' => true,
    ];

    /**
     * @param array $fields
     * @return static
     */
    final public function setFullTextSearchFields(array $fields)
    {
        $this->fullTextSearchFields = array_flip($fields);
        return $this;
    }

    /**
     * @return array
     */
    final public function getFullTextSearchFields()
    {
        return array_keys($this->fullTextSearchFields);
    }

    /**
     * @param string $field
     * @return bool
     */
    final public function supportsFullTextSearch($field)
    {
        $field = trim(strtolower($field));
        if (empty($field)) {
            return false;
        }

        return isset($this->fullTextSearchFields[$field]);
    }

    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    final public function addParsedQuery(ParsedQuery $parsedQuery)
    {
        $this->parsedQuery = $parsedQuery;
        $this->beforeAddParsedQuery($parsedQuery);

        /** @var QueryBuilder $this */
        foreach ($parsedQuery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        return $this;
    }

    /**
     * @param ParsedQuery $parsedQuery
     */
    protected function beforeAddParsedQuery(ParsedQuery $parsedQuery)
    {
    }

    /**
     * @param Date $date
     * @return static
     */
    final public function addDate(Date $date)
    {
        $this->handleTerm($date);
        return $this;
    }

    /**
     * @param DateRange $dateRange
     * @return static
     */
    final public function addDateRange(DateRange $dateRange)
    {
        $this->inRange = true;
        $this->startRange($dateRange);
        $this->endRange($dateRange);
        $this->inRange = false;
        return $this;
    }

    /**
     * @param Emoji $emoji
     * @return static
     */
    final public function addEmoji(Emoji $emoji)
    {
        $this->handleTerm($emoji);
        return $this;
    }

    /**
     * @param Emoticon $emoticon
     * @return static
     */
    final public function addEmoticon(Emoticon $emoticon)
    {
        $this->handleTerm($emoticon);
        return $this;
    }

    /**
     * @param Field $field
     * @return static
     */
    final public function addField(Field $field)
    {
        $this->inField = true;
        $this->currentField = $field;
        $this->startField($field);

        switch ($field->getFieldType()->getValue()) {
            case FieldType::SIMPLE:
                $node = $field->getNode();
                break;

            case FieldType::RANGE:
                $node = $field->getRange();
                break;

            case FieldType::SUBQUERY:
                $node = $field->getSubquery();
                break;

            default:
                $node = null;
                break;
        }

        if ($node instanceof Node) {
            $node->acceptBuilder($this);
        }

        $this->endField($field);
        $this->inField = false;
        $this->currentField = null;

        return $this;
    }

    /**
     * @param Hashtag $hashtag
     * @return static
     */
    final public function addHashtag(Hashtag $hashtag)
    {
        $this->handleTerm($hashtag);
        return $this;
    }

    /**
     * @param Mention $mention
     * @return static
     */
    final public function addMention(Mention $mention)
    {
        $this->handleTerm($mention);
        return $this;
    }

    /**
     * @param \Gdbots\QueryParser\Node\Number $number
     * @return static
     */
    final public function addNumber(Number $number)
    {
        $this->handleTerm($number);
        return $this;
    }

    /**
     * @param NumberRange $numberRange
     * @return static
     */
    final public function addNumberRange(NumberRange $numberRange)
    {
        // todo: exception for range not in filter or in subquery
        if ($this->inField && !$this->inRange && !$this->inSubquery) {
            $this->inRange = true;
            $this->startRange($numberRange);
            $this->endRange($numberRange);
            $this->inRange = false;
            return $this;
        }

        throw new \LogicException('A NumberRange can only be used within a filter.  e.g. rating:[1..5]');
    }

    /**
     * @param Phrase $phrase
     * @return static
     */
    final public function addPhrase(Phrase $phrase)
    {
        $this->handleText($phrase);
        return $this;
    }

    /**
     * @param Subquery $subquery
     * @return static
     */
    final public function addSubquery(Subquery $subquery)
    {
        $this->inSubquery = true;
        $this->startSubquery($subquery);

        /** @var QueryBuilder|self $this */
        foreach ($subquery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        $this->endSubquery($subquery);
        $this->inSubquery = false;

        return $this;
    }

    /**
     * @param Url $url
     * @return static
     */
    final public function addUrl(Url $url)
    {
        $this->handleTerm($url);
        return $this;
    }

    /**
     * @param Word $word
     * @return static
     */
    final public function addWord(Word $word)
    {
        /*
         * if is a stop word, then always handle as optional term (should match)
         * if in filter...
         * - pass field name and value to "isFieldAnalyzed"?
         *      - if true use "should match"
         *      - if false, use handle explicit
         * -
         *
         */
        $this->handleText($word);
        return $this;
    }

    /**
     * @param WordRange $wordRange
     * @return static
     */
    final public function addWordRange(WordRange $wordRange)
    {
        $this->inRange = true;
        $this->startRange($wordRange);
        $this->endRange($wordRange);
        $this->inRange = false;
        $this->currentRange = null;

        return $this;
    }

    /**
     * @param Node $node
     */
    protected function handleText(Node $node)
    {
        if ($node instanceof Word && $node->isStopWord()) {
            $this->shouldMatchText($node);
            return;
        }

        if ($this->inField && !$this->supportsFullTextSearch($this->currentField->getName())) {
            $this->handleTerm($node);
            return;
        }

        if ($node->isOptional()) {
            $this->shouldMatchText($node);
        } elseif ($node->isRequired()) {
            $this->mustMatchText($node);
        } elseif ($node->isProhibited()) {
            $this->mustNotMatchText($node);
        }
    }

    /**
     * @param Node $node
     */
    protected function handleTerm(Node $node)
    {
        if ($this->inField) {
            /*
             * When in a simple field, the bool operator is based on
             * the field, not the node in the field.
             */
            if ($this->currentField->hasSimpleValue()) {
                if ($this->currentField->isOptional()) {
                    $this->shouldMatchTerm($node);
                    return;
                } elseif ($node->isRequired()) {
                    $this->mustMatchTerm($node);
                    return;
                }

                $this->mustNotMatchTerm($node);
            }
        }

        if ($node->isOptional()) {
            $this->shouldMatchTerm($node);
            return;
        } elseif ($node->isRequired()) {
            $this->mustMatchTerm($node);
            return;
        }

        $this->mustNotMatchTerm($node);
    }

    /**
     * @param Field $field
     */
    protected function startField(Field $field) {}

    /**
     * @param Field $field
     */
    protected function endField(Field $field) {}

    /**
     * @param Range $range
     */
    protected function startRange(Range $range) {}

    /**
     * @param Range $range
     */
    protected function endRange(Range $range) {}

    /**
     * @param Subquery $subquery
     */
    protected function startSubquery(Subquery $subquery) {}

    /**
     * @param Subquery $subquery
     */
    protected function endSubquery(Subquery $subquery) {}

    /**
     * @param Node $node
     */
    abstract protected function mustMatchText(Node $node);

    /**
     * @param Node $node
     */
    abstract protected function shouldMatchText(Node $node);

    /**
     * @param Node $node
     */
    abstract protected function mustNotMatchText(Node $node);

    /**
     * @param Node $node
     */
    abstract protected function mustMatchTerm(Node $node);

    /**
     * @param Node $node
     */
    abstract protected function shouldMatchTerm(Node $node);

    /**
     * @param Node $node
     */
    abstract protected function mustNotMatchTerm(Node $node);
}
