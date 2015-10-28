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
     * An array containing strings with an error message for every expression
     * that could not be parsed.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The parsed QueryItem instance
     *
     * @var QueryItem
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
     * __construct
     */
    public function __construct()
    {
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
        $this->parser->readString($queryString, true);
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
     * @return \Gdbots\QueryParser\Node\QueryItem
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
        return $this->filters ?: $this->filters = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_FILTER);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Hashtag[]
     */
    public function getHashtags()
    {
        return $this->hashtags ?: $this->hashtags = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_HASHTAG);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Mention[]
     */
    public function getMentions()
    {
        return $this->mentions ?: $this->mentions = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_MENTION);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Phrase[]
     */
    public function getPhrases()
    {
        return $this->phrases ?: $this->phrases = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_PHRASE);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getWords()
    {
        return $this->words ?: $this->words = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_WORD);
    }

    /**
     * @return \Gdbots\QueryParser\Node\Url[]
     */
    public function getUrls()
    {
        return $this->urls ?: $this->urls = $this->queryTokens->getQueryItemsByTokenType(QueryScanner::T_URL);
    }
}
