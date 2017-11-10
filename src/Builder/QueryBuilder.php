<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Numbr;
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
    public function clear(): QueryBuilder;

    /**
     * Sets the fields that this builder will enable for full text search.
     *
     * @param string[] $fields
     *
     * @return static
     */
    public function setFullTextSearchFields(array $fields): QueryBuilder;

    /**
     * Adds a field that this builder will enable for full text search.
     *
     * @param string $fieldName
     *
     * @return static
     */
    public function addFullTextSearchField(string $fieldName): QueryBuilder;

    /**
     * Removes a field that was previously enabled for full text search.
     *
     * @param string $fieldName
     *
     * @return static
     */
    public function removeFullTextSearchField(string $fieldName): QueryBuilder;

    /**
     * Gets the fields enabled for full text search.
     *
     * @return string[]
     */
    public function getFullTextSearchFields(): array;

    /**
     * Returns true if the given field supports full text searching.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function supportsFullTextSearch(string $fieldName): bool;

    /**
     * Sets the default field that will be searched when a query
     * doesn't explicitly set it.
     *
     * @param string $fieldName
     *
     * @return static
     */
    public function setDefaultFieldName(string $fieldName): QueryBuilder;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setEmojiFieldName(string $fieldName): QueryBuilder;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setEmoticonFieldName(string $fieldName): QueryBuilder;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setHashtagFieldName(string $fieldName): QueryBuilder;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setMentionFieldName(string $fieldName): QueryBuilder;

    /**
     * @param \DateTimeZone $timeZone
     *
     * @return static
     */
    public function setLocalTimeZone(\DateTimeZone $timeZone): QueryBuilder;

    /**
     * @param ParsedQuery $parsedQuery
     *
     * @return static
     */
    public function addParsedQuery(ParsedQuery $parsedQuery): QueryBuilder;

    /**
     * @param Date $date
     *
     * @return static
     */
    public function addDate(Date $date): QueryBuilder;

    /**
     * @param Emoji $emoji
     *
     * @return static
     */
    public function addEmoji(Emoji $emoji): QueryBuilder;

    /**
     * @param Emoticon $emoticon
     *
     * @return static
     */
    public function addEmoticon(Emoticon $emoticon): QueryBuilder;

    /**
     * @param Field $field
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addField(Field $field): QueryBuilder;

    /**
     * @param Hashtag $hashtag
     *
     * @return static
     */
    public function addHashtag(Hashtag $hashtag): QueryBuilder;

    /**
     * @param Mention $mention
     *
     * @return static
     */
    public function addMention(Mention $mention): QueryBuilder;

    /**
     * @param Numbr $number
     *
     * @return static
     */
    public function addNumber(Numbr $number): QueryBuilder;

    /**
     * @param Phrase $phrase
     *
     * @return static
     */
    public function addPhrase(Phrase $phrase): QueryBuilder;

    /**
     * @param Range $range
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addRange(Range $range): QueryBuilder;

    /**
     * @param Subquery $subquery
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addSubquery(Subquery $subquery): QueryBuilder;

    /**
     * @param Url $url
     *
     * @return static
     */
    public function addUrl(Url $url): QueryBuilder;

    /**
     * @param Word $word
     *
     * @return static
     */
    public function addWord(Word $word): QueryBuilder;
}
