<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Filter;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\NumberRange;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\WordRange;
use Gdbots\QueryParser\ParsedQuery;

interface QueryBuilder
{
    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    public function fromParsedQuery(ParsedQuery $parsedQuery);

    /**
     * @param Date $date
     * @return static
     */
    public function addDate(Date $date);

    /**
     * @param DateRange $dateRange
     * @return static
     */
    public function addDateRange(DateRange $dateRange);

    /**
     * @param Emoji $emoji
     * @return static
     */
    public function addEmoji(Emoji $emoji);

    /**
     * @param Emoticon $emoticon
     * @return static
     */
    public function addEmoticon(Emoticon $emoticon);

    /**
     * @param Filter $filter
     * @return static
     */
    public function addFilter(Filter $filter);

    /**
     * @param Hashtag $hashtag
     * @return static
     */
    public function addHashtag(Hashtag $hashtag);

    /**
     * @param Mention $mention
     * @return static
     */
    public function addMention(Mention $mention);

    /**
     * @param \Gdbots\QueryParser\Node\Number $number
     * @return static
     */
    public function addNumber(Number $number);

    /**
     * @param NumberRange $numberRange
     * @return static
     */
    public function addNumberRange(NumberRange $numberRange);

    /**
     * @param Phrase $phrase
     * @return static
     */
    public function addPhrase(Phrase $phrase);

    /**
     * @param Subquery $subquery
     * @return static
     */
    public function addSubquery(Subquery $subquery);

    /**
     * @param Url $url
     * @return static
     */
    public function addUrl(Url $url);

    /**
     * @param Word $word
     * @return static
     */
    public function addWord(Word $word);

    /**
     * @param WordRange $wordRange
     * @return static
     */
    public function addWordRange(WordRange $wordRange);
}
