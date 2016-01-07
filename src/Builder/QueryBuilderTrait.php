<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Enum\FilterType;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Filter;
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

trait QueryBuilderTrait
{
    /** @var ParsedQuery */
    protected $parsedQuery;

    /**
     * The builder is currently handling a Filter.
     * @var bool
     */
    protected $inFilter = false;

    /** @var Filter */
    protected $currentFilter;

    /**
     * The builder is currently handling a Range.
     * @var bool
     */
    protected $inRange = false;

    /** @var Range */
    protected $currentRange;

    /**
     * The builder is currently handling a Subquery.
     * @var bool
     */
    protected $inSubquery = false;

    /** @var Subquery */
    protected $currentSubquery;

    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    public function fromParsedQuery(ParsedQuery $parsedQuery)
    {
        $this->parsedQuery = $parsedQuery;
        $this->beforeFromParsedQuery($parsedQuery);

        /** @var QueryBuilder $this */
        foreach ($parsedQuery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        return $this;
    }

    /**
     * @param ParsedQuery $parsedQuery
     */
    protected function beforeFromParsedQuery(ParsedQuery $parsedQuery)
    {
        // override to customize
    }

    /**
     * @param Date $date
     * @return static
     */
    public function addDate(Date $date)
    {
        $this->handleNumericTerm($date);
        return $this;
    }

    /**
     * @param DateRange $dateRange
     * @return static
     */
    public function addDateRange(DateRange $dateRange)
    {
        $this->inRange = true;
        $this->currentRange = $dateRange;
        $this->startRange($dateRange);
        $this->endRange($dateRange);
        $this->inRange = false;
        $this->currentRange = null;

        return $this;
    }

    /**
     * @param Emoji $emoji
     * @return static
     */
    public function addEmoji(Emoji $emoji)
    {
        $this->handleExplicitTerm($emoji);
        return $this;
    }

    /**
     * @param Emoticon $emoticon
     * @return static
     */
    public function addEmoticon(Emoticon $emoticon)
    {
        $this->handleExplicitTerm($emoticon);
        return $this;
    }

    /**
     * @param Filter $filter
     * @return static
     */
    public function addFilter(Filter $filter)
    {
        $this->inFilter = true;
        $this->currentFilter = $filter;
        $this->startFilter($filter);

        /** @var QueryBuilder|self $this */
        switch ($filter->getFilterType()->getValue()) {
            case FilterType::SIMPLE:
                $node = $filter->getNode();
                break;

            case FilterType::RANGE:
                $node = $filter->getRange();
                break;

            case FilterType::SUBQUERY:
                $node = $filter->getSubquery();
                break;

            default:
                $node = null;
                break;
        }

        if ($node instanceof Node) {
            $node->acceptBuilder($this);
        }

        $this->endFilter($filter);
        $this->inFilter = false;
        $this->currentFilter = null;

        return $this;
    }

    /**
     * @param Hashtag $hashtag
     * @return static
     */
    public function addHashtag(Hashtag $hashtag)
    {
        $this->handleExplicitTerm($hashtag);
        return $this;
    }

    /**
     * @param Mention $mention
     * @return static
     */
    public function addMention(Mention $mention)
    {
        $this->handleExplicitTerm($mention);
        return $this;
    }

    /**
     * @param \Gdbots\QueryParser\Node\Number $number
     * @return static
     */
    public function addNumber(Number $number)
    {
        $this->handleNumericTerm($number);
        return $this;
    }

    /**
     * @param NumberRange $numberRange
     * @return static
     */
    public function addNumberRange(NumberRange $numberRange)
    {
        $this->inRange = true;
        $this->currentRange = $numberRange;
        $this->startRange($numberRange);
        $this->endRange($numberRange);
        $this->inRange = false;
        $this->currentRange = null;

        return $this;
    }

    /**
     * @param Phrase $phrase
     * @return static
     */
    public function addPhrase(Phrase $phrase)
    {
        $this->handleTerm($phrase);
        return $this;
    }

    /**
     * @param Subquery $subquery
     * @return static
     */
    public function addSubquery(Subquery $subquery)
    {
        $this->inSubquery = true;
        $this->currentSubquery = $subquery;
        $this->startSubquery($subquery);

        /** @var QueryBuilder|self $this */
        foreach ($subquery->getNodes() as $node) {
            $node->acceptBuilder($this);
        }

        $this->endSubquery($subquery);
        $this->inSubquery = false;
        $this->currentSubquery = null;

        return $this;
    }

    /**
     * @param Url $url
     * @return static
     */
    public function addUrl(Url $url)
    {
        $this->handleExplicitTerm($url);
        return $this;
    }

    /**
     * @param Word $word
     * @return static
     */
    public function addWord(Word $word)
    {
        $this->handleTerm($word);
        return $this;
    }

    /**
     * @param WordRange $wordRange
     * @return static
     */
    public function addWordRange(WordRange $wordRange)
    {
        $this->inRange = true;
        $this->currentRange = $wordRange;
        $this->startRange($wordRange);
        $this->endRange($wordRange);
        $this->inRange = false;
        $this->currentRange = null;

        return $this;
    }

    /**
     * @param Node $node
     */
    protected function handleTerm(Node $node)
    {
        /*
         * Override to provide handling for analyzed/indexed terms.
         *
         * This trait will push these types to this method:
         * - Word
         * - Phrase
         *
         */
    }

    protected function handleExplicitTerm(Node $node)
    {
        /*
         * Override to provide handling for explicit terms (typically not analyzed).
         * Values like hashtags, usernames, url, enums, etc.
         *
         * This trait will push these types to this method:
         * - Emoji
         * - Emoticon
         * - Hashtag
         * - Mention
         * - Url
         *
         */
    }

    /**
     * @param Node $node
     */
    protected function handleNumericTerm(Node $node)
    {
        /*
         * Override to provide handling for numbers and dates.
         *
         * This trait will push these types to this method:
         * - Date
         * - Number
         *
         */
    }

    /**
     * @param Filter $filter
     */
    protected function startFilter(Filter $filter)
    {
        // override to customize
    }

    /**
     * @param Filter $filter
     */
    protected function endFilter(Filter $filter)
    {
        // override to customize
    }

    /**
     * @param Range $range
     */
    protected function startRange(Range $range)
    {
        // override to customize
    }

    /**
     * @param Range $range
     */
    protected function endRange(Range $range)
    {
        // override to customize
    }

    /**
     * @param Subquery $subquery
     */
    protected function startSubquery(Subquery $subquery)
    {
        // override to customize
    }

    /**
     * @param Subquery $subquery
     */
    protected function endSubquery(Subquery $subquery)
    {
        // override to customize
    }
}
