<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Numbr;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\WordRange;
use Gdbots\QueryParser\ParsedQuery;

abstract class AbstractQueryBuilder implements QueryBuilder
{
    /** @var Field */
    private $currentField;

    /** @var bool */
    private $queryOnFieldIsCacheable = false;

    /** @var bool */
    private $inField = false;

    /** @var bool */
    private $inRange = false;

    /** @var bool */
    private $inSubquery = false;

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
        'story' => true,
        'html' => true,
        'text' => true,
        'markdown' => true,
        'content' => true,
        'contents' => true,
        'contents-continued' => true,
        'contents-md' => true,
        'contents-mobile' => true,
        'mobile-contents' => true,
        'txt-contents' => true,
        'text-contents' => true,
        'abstract' => true,
        'search_text' => true,
        'cover' => true,
        'bio' => true,
        'mini_bio' => true,
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'og_title' => true,
        'og_description' => true,
        'og_keywords' => true,
        'seo_title' => true,
        'seo_description' => true,
        'seo_keywords' => true,
        'img_credit' => true,
        'img_caption' => true,
        'credit' => true,
        'caption' => true,
        'img_credits' => true,
        'img_captions' => true,
        'image_credits' => true,
        'image_captions' => true,
        'credits' => true,
        'captions' => true,
        'full_name' => true,
        'first_name' => true,
        'last_name' => true,
        'street1' => true,
        'street2' => true,
        'city' => true,
        'address.street1' => true,
        'address.street2' => true,
        'address.city' => true,
        'ctx_ip_geo.street1' => true,
        'ctx_ip_geo.street2' => true,
        'ctx_ip_geo.city' => true,
    ];

    /** @var string */
    protected $defaultFieldName;

    /** @var string */
    protected $emojiFieldName;

    /** @var string */
    protected $emoticonFieldName;

    /** @var string */
    protected $hashtagFieldName;

    /** @var string */
    protected $mentionFieldName;

    /** @var \DateTimeZone */
    protected $localTimeZone;

    /**
     * @return static
     */
    public function clear()
    {
        return $this;
    }

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
     * @param string $fieldName
     * @return static
     */
    final public function addFullTextSearchField($fieldName)
    {
        $this->fullTextSearchFields[$fieldName] = true;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function removeFullTextSearchField($fieldName)
    {
        unset($this->fullTextSearchFields[$fieldName]);
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
     * @param string $fieldName
     * @return bool
     */
    final public function supportsFullTextSearch($fieldName)
    {
        return isset($this->fullTextSearchFields[trim(strtolower($fieldName))]);
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function setDefaultFieldName($fieldName)
    {
        $this->defaultFieldName = $fieldName;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function setEmojiFieldName($fieldName)
    {
        $this->emojiFieldName = $fieldName;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function setEmoticonFieldName($fieldName)
    {
        $this->emoticonFieldName = $fieldName;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function setHashtagFieldName($fieldName)
    {
        $this->hashtagFieldName = $fieldName;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    final public function setMentionFieldName($fieldName)
    {
        $this->mentionFieldName = $fieldName;
        return $this;
    }

    /**
     * @param \DateTimeZone $timeZone
     * @return static
     */
    final public function setLocalTimeZone(\DateTimeZone $timeZone)
    {
        $this->localTimeZone = $timeZone;
        return $this;
    }

    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    final public function addParsedQuery(ParsedQuery $parsedQuery)
    {
        foreach ($parsedQuery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        return $this;
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
        if ($this->inField || null === $this->emojiFieldName) {
            $this->handleTerm($emoji);
            return $this;
        }

        $field = new Field(
            $this->emojiFieldName,
            $emoji,
            $emoji->getBoolOperator(),
            $emoji->useBoost(),
            $emoji->getBoost()
        );

        return $this->addField($field);
    }

    /**
     * @param Emoticon $emoticon
     * @return static
     */
    final public function addEmoticon(Emoticon $emoticon)
    {
        if ($this->inField || null === $this->emoticonFieldName) {
            $this->handleTerm($emoticon);
            return $this;
        }

        $field = new Field(
            $this->emoticonFieldName,
            $emoticon,
            $emoticon->getBoolOperator(),
            $emoticon->useBoost(),
            $emoticon->getBoost()
        );

        return $this->addField($field);
    }

    /**
     * @param Field $field
     * @return static
     *
     * @throws \LogicException
     */
    final public function addField(Field $field)
    {
        if ($this->inField || $this->inRange) {
            throw new \LogicException('A Field cannot be nested in another Field or Range.');
        }

        $this->inField = true;
        $this->currentField = $field;
        $this->queryOnFieldIsCacheable = $this->queryOnFieldIsCacheable($field);
        $this->startField($field, $this->queryOnFieldIsCacheable);
        $field->getNode()->acceptBuilder($this);
        $this->endField($field, $this->queryOnFieldIsCacheable);
        $this->inField = false;
        $this->currentField = null;
        $this->queryOnFieldIsCacheable = false;
        return $this;
    }

    /**
     * @param Hashtag $hashtag
     * @return static
     */
    final public function addHashtag(Hashtag $hashtag)
    {
        if ($this->inField || null === $this->hashtagFieldName) {
            $this->handleTerm($hashtag);
            return $this;
        }

        $field = new Field(
            $this->hashtagFieldName,
            $hashtag,
            $hashtag->getBoolOperator(),
            $hashtag->useBoost(),
            $hashtag->getBoost()
        );

        return $this->addField($field);
    }

    /**
     * @param Mention $mention
     * @return static
     */
    final public function addMention(Mention $mention)
    {
        if ($this->inField || null === $this->mentionFieldName) {
            $this->handleTerm($mention);
            return $this;
        }

        $field = new Field(
            $this->mentionFieldName,
            $mention,
            $mention->getBoolOperator(),
            $mention->useBoost(),
            $mention->getBoost()
        );

        return $this->addField($field);
    }

    /**
     * @param Numbr $number
     * @return static
     */
    final public function addNumber(Numbr $number)
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
     *
     * @throws \LogicException
     */
    final public function addRange(Range $range)
    {
        if (!$this->inField || $this->inRange || $this->inSubquery) {
            throw new \LogicException('A Range can only be used within a field.  e.g. rating:[1..5]');
        }

        $this->inRange = true;
        $this->handleRange($range, $this->currentField, $this->queryOnFieldIsCacheable);
        $this->inRange = false;
        return $this;
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
        $this->startSubquery($subquery, $this->currentField);

        foreach ($subquery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        $this->endSubquery($subquery, $this->currentField);
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
     * @return bool
     */
    final protected function inField()
    {
        return $this->inField;
    }

    /**
     * @return bool
     */
    final protected function inRange()
    {
        return $this->inRange;
    }

    /**
     * @return bool
     */
    final protected function inSubquery()
    {
        return $this->inSubquery;
    }

    /**
     * @param Node $node
     */
    private function handleText(Node $node)
    {
        if ($this->inField && !$this->supportsFullTextSearch($this->currentField->getName())) {
            $this->handleTerm($node);
            return;
        }

        if ($node instanceof Word && $node->isStopWord()) {
            $this->shouldMatch($node, $this->currentField);
            return;
        } elseif ($node->isOptional()) {
            $this->shouldMatch($node, $this->currentField);
            return;
        } elseif ($node->isRequired()) {
            $this->mustMatch($node, $this->currentField);
            return;
        }

        $this->mustNotMatch($node, $this->currentField);
    }

    /**
     * @param Node $node
     */
    private function handleTerm(Node $node)
    {
        /*
         * When in a simple field, the bool operator is based on
         * the field, not the node in the field.
         * +field:value vs. field:+value
         */
        if ($this->inField && !$this->currentField->hasCompoundNode()) {
            if ($this->currentField->isOptional()) {
                $this->shouldMatchTerm($node, $this->currentField);
                return;
            } elseif ($this->currentField->isRequired()) {
                $this->mustMatchTerm($node, $this->currentField, $this->queryOnFieldIsCacheable);
                return;
            }

            $this->mustNotMatchTerm($node, $this->currentField, $this->queryOnFieldIsCacheable);
            return;
        }

        if ($node->isOptional()) {
            $this->shouldMatchTerm($node, $this->currentField);
            return;
        } elseif ($node->isRequired()) {
            $this->mustMatchTerm($node, $this->currentField, $this->queryOnFieldIsCacheable);
            return;
        }

        $this->mustNotMatchTerm($node, $this->currentField, $this->queryOnFieldIsCacheable);
    }

    /**
     * If the query on this particular field could be cached because it contains
     * only exact values, is not optional or boosted then the storage/search
     * provider might be able to cache the resultset or optimize the query
     * against this field.
     *
     * This is typically used on required fields that will prefilter the
     * results that will be searched on.  For example, find all videos
     * with "cats" in them that are "status:active".  It makes no sense
     * to even search for cats in a video when status is not active.
     *
     * @param Field $field
     * @return bool
     */
    protected function queryOnFieldIsCacheable(Field $field)
    {
        if ($field->isOptional() || $field->useBoost()) {
            return false;
        }

        $node = $field->getNode();
        if ($node->useFuzzy()
            || $this->supportsFullTextSearch($field->getName())
            || $node instanceof Subquery
            || $node instanceof WordRange
            || $node instanceof Phrase
            || ($node instanceof Word && $node->hasTrailingWildcard())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Field $field
     * @param bool $cacheable
     */
    protected function startField(Field $field, $cacheable = false) {}

    /**
     * @param Field $field
     * @param bool $cacheable
     */
    protected function endField(Field $field, $cacheable = false) {}

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function startSubquery(Subquery $subquery, Field $field = null) {}

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function endSubquery(Subquery $subquery, Field $field = null) {}

    /**
     * @param Range $range
     * @param Field $field
     * @param bool $cacheable
     */
    abstract protected function handleRange(Range $range, Field $field, $cacheable = false);

    /**
     * @param Node $node
     * @param Field|null $field
     */
    abstract protected function mustMatch(Node $node, Field $field = null);

    /**
     * @param Node $node
     * @param Field|null $field
     */
    abstract protected function shouldMatch(Node $node, Field $field = null);

    /**
     * @param Node $node
     * @param Field|null $field
     */
    abstract protected function mustNotMatch(Node $node, Field $field = null);

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    abstract protected function mustMatchTerm(Node $node, Field $field = null, $cacheable = false);

    /**
     * @param Node $node
     * @param Field|null $field
     */
    abstract protected function shouldMatchTerm(Node $node, Field $field = null);

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    abstract protected function mustNotMatchTerm(Node $node, Field $field = null, $cacheable = false);
}
