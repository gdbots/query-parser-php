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
     * Resets the builder so any nodes added are cleared and
     * you can build a new query.  Any builder options/settings
     * should be maintained after a clear.
     *
     * @return static
     */
    public function clear();

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
     * @param string $fieldName
     * @return bool
     */
    public function supportsFullTextSearch($fieldName);

    /**
     * Sets the default field that will be searched when a query
     * doesn't explicitly set it.
     *
     * @param string $fieldName
     * @return static
     */
    public function setDefaultFieldName($fieldName);

    /**
     * @param string $fieldName
     * @return static
     */
    public function setEmojiFieldName($fieldName);

    /**
     * @param string $fieldName
     * @return static
     */
    public function setEmoticonFieldName($fieldName);

    /**
     * @param string $fieldName
     * @return static
     */
    public function setHashtagFieldName($fieldName);

    /**
     * @param string $fieldName
     * @return static
     */
    public function setMentionFieldName($fieldName);

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
     *
     * @throws \LogicException
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
     *
     * @throws \LogicException
     */
    public function addRange(Range $range);

    /**
     * @param Subquery $subquery
     * @return static
     *
     * @throws \LogicException
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
