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
    public function clear(): self;

    /**
     * Sets the fields that this builder will enable for full text search.
     *
     * @param string[] $fields
     *
     * @return static
     */
    public function setFullTextSearchFields(array $fields): self;

    /**
     * Adds a field that this builder will enable for full text search.
     *
     * @param string $fieldName
     *
     * @return static
     */
    public function addFullTextSearchField(string $fieldName): self;

    /**
     * Removes a field that was previously enabled for full text search.
     *
     * @param string $fieldName
     *
     * @return static
     */
    public function removeFullTextSearchField(string $fieldName): self;

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
    public function setDefaultFieldName(string $fieldName): self;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setEmojiFieldName(string $fieldName): self;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setEmoticonFieldName(string $fieldName): self;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setHashtagFieldName(string $fieldName): self;

    /**
     * @param string $fieldName
     *
     * @return static
     */
    public function setMentionFieldName(string $fieldName): self;

    /**
     * @param \DateTimeZone $timeZone
     *
     * @return static
     */
    public function setLocalTimeZone(\DateTimeZone $timeZone): self;

    /**
     * @param ParsedQuery $parsedQuery
     *
     * @return static
     */
    public function addParsedQuery(ParsedQuery $parsedQuery): self;

    /**
     * @param Date $date
     *
     * @return static
     */
    public function addDate(Date $date): self;

    /**
     * @param Emoji $emoji
     *
     * @return static
     */
    public function addEmoji(Emoji $emoji): self;

    /**
     * @param Emoticon $emoticon
     *
     * @return static
     */
    public function addEmoticon(Emoticon $emoticon): self;

    /**
     * @param Field $field
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addField(Field $field): self;

    /**
     * @param Hashtag $hashtag
     *
     * @return static
     */
    public function addHashtag(Hashtag $hashtag): self;

    /**
     * @param Mention $mention
     *
     * @return static
     */
    public function addMention(Mention $mention): self;

    /**
     * @param Numbr $number
     *
     * @return static
     */
    public function addNumber(Numbr $number): self;

    /**
     * @param Phrase $phrase
     *
     * @return static
     */
    public function addPhrase(Phrase $phrase): self;

    /**
     * @param Range $range
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addRange(Range $range): self;

    /**
     * @param Subquery $subquery
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function addSubquery(Subquery $subquery): self;

    /**
     * @param Url $url
     *
     * @return static
     */
    public function addUrl(Url $url): self;

    /**
     * @param Word $word
     *
     * @return static
     */
    public function addWord(Word $word): self;
}
