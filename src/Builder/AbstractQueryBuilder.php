<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\ParsedQuery;

/*
 * DEV NOTES...
 *
 * When is it a term? (exact value)
 * - if it's a Date, Emoji, Emoticon, Hashtag, Mention, Number, Url
 * - if it's in a field and the field does not support full text search. i.e. "+status:active"
 *
 *
 * When should "filter" be used?
 * - when a "term" is required or prohibited.
 *
 *
 * When should "should match" be used?
 * - when a word or phrase is not required or prohibited.
 * - when a word that is required is a stop word (possible/likely it's not even in the index).
 * - when a word that is required uses fuzzy or trailing wildcard
 *
 *
 * When should "should match term" be used?
 * - when a term is not in a field node and is not required.
 *
 *
 */
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

    /** @var string */
    protected $defaultField;

    /**
     * The field that Hashtag nodes will be searched in unless already
     * within a Field object.  If null the Hashtag will be handled
     * as a term and queried in the default field.
     *
     * @var string
     */
    protected $hashtagField;

    /**
     * The field that Mention nodes will be searched in unless already
     * within a Field object.  If null the Mention will be handled
     * as a term and queried in the default field.
     *
     * @var string
     */
    protected $mentionField;

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
        'overview' => true,
        'summary' => true,
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
        return isset($this->fullTextSearchFields[trim(strtolower($field))]);
    }

    /**
     * @param string $field
     * @return static
     */
    final public function setDefaultField($field)
    {
        $this->defaultField = $field;
        return $this;
    }

    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    final public function addParsedQuery(ParsedQuery $parsedQuery)
    {
        $this->parsedQuery = $parsedQuery;
        $this->beforeAddParsedQuery($parsedQuery);

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
        $field->getNode()->acceptBuilder($this);
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
     * @param Phrase $phrase
     * @return static
     */
    final public function addPhrase(Phrase $phrase)
    {
        $this->handleText($phrase);
        return $this;
    }

    /**
     * @param Range $range
     * @return static
     */
    final public function addRange(Range $range)
    {
        if ($this->inField && !$this->inRange && !$this->inSubquery) {
            $this->inRange = true;
            $this->handleRange($range);
            $this->inRange = false;
            return $this;
        }

        throw new \LogicException('A Range can only be used within a filter.  e.g. rating:[1..5]');
    }

    /**
     * @param Subquery $subquery
     * @return static
     */
    final public function addSubquery(Subquery $subquery)
    {
        if ($this->inRange || $this->inSubquery) {
            throw new \LogicException('A Subquery cannot be nested or within a Range.');
        }

        $this->inSubquery = true;
        $this->startSubquery($subquery);

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
        $this->handleText($word);
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
            if (!$this->currentField->hasCompoundNode()) {
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
    protected function handleRange(Range $range) {}

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
