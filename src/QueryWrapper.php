<?php

namespace Gdbots\QueryParser;

/**
 * Takes a query string and returns an object with different classified token types.
 * The type can be : Filter, Hashtag, Mention, Phrase, Word, Url.
 * Each token can have one or more attributes:  boost, include, exclude
 */
class QueryWrapper
{
    /**
     * @var QueryParser
     */
    protected $parser;

    /**
     * @var bool
     */
    protected $ignoreOperator = true;

    /**
     * The parsed AbstractQueryItem instance
     *
     * @var AbstractQueryItem
     */
    protected $queryItem = null;

    /**
     * An array containing filters that was parsed
     *
     * @var array
     */
    protected $filters = [];

    /**
     * An array containing hashtags that was parsed
     *
     * @var array
     */
    protected $hashtags = [];

    /**
     * An array containing mentions that was parsed
     *
     * @var array
     */
    protected $mentions = [];

    /**
     * An array containing phrases that was parsed
     *
     * @var array
     */
    protected $phrases = [];

    /**
     * An array containing words that was parsed
     *
     * @var array
     */
    protected $words = [];

    /**
     * An array containing words that was parsed
     *
     * @var array
     */
    protected $urls = [];

    /**
     * @param bool $ignoreOperator
     */
    public function __construct($ignoreOperator = true)
    {
        $this->ignoreOperator = $ignoreOperator;

        $this->parser = new QueryParser();
    }

    /**
     * Parses Query String and returns array of tokens.
     *
     * @param string $queryString
     *
     * @return self
     */
    public function parse($queryString)
    {
        $this->parser->readString($queryString, $this->ignoreOperator);
        $this->queryItem = $this->parser->parse();

        return $this;
    }

    /**
     * @return QueryParser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem
     */
    public function getParseResultQueryItem()
    {
        return $this->queryItem;
    }

    /**
     * @return \Gdbots\QueryParser\Node\ExplicitTerm[]
     */
    public function getFilters()
    {
        return $this->filters ?: $this->filters = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_FILTER);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Hashtag[]
     */
    public function getHashtags()
    {
        return $this->hashtags ?: $this->hashtags = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_HASHTAG);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Mention[]
     */
    public function getMentions()
    {
        return $this->mentions ?: $this->mentions = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_MENTION);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Phrase[]
     */
    public function getPhrases()
    {
        return $this->phrases ?: $this->phrases = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_PHRASE);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getWords()
    {
        return $this->words ?: $this->words = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_WORD);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Url[]
     */
    public function getUrls()
    {
        return $this->urls ?: $this->urls = $this->queryItem->getQueryItemsByTokenType(QueryLexer::T_URL);
    }
}
