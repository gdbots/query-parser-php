<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\ParsedQuery;

interface QueryBuilder
{
    /**
     * Sets the fields that this builder will enable for full text search.
     *
     * @param array $fields
     * @return static
     */
    public function setFullTextSearchFields(array $fields);

    /**
     * Gets the fields enabled for full text search.
     *
     * @return array
     */
    public function getFullTextSearchFields();

    /**
     * Returns true if the given field supports full text searching.
     *
     * @param string $field
     * @return bool
     */
    public function supportsFullTextSearch($field);

    /**
     * @param ParsedQuery $parsedQuery
     * @return static
     */
    public function addParsedQuery(ParsedQuery $parsedQuery);

    /**
     * @param Date $date
     * @return static
     */
    public function addDate(Date $date);

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
     * @param Field $field
     * @return static
     */
    public function addField(Field $field);

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
     * @param Phrase $phrase
     * @return static
     */
    public function addPhrase(Phrase $phrase);

    /**
     * @param Range $range
     * @return static
     */
    public function addRange(Range $range);

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
}
