<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Node;

/**
 * Takes a query string and returns an object with different classified token types.
 * The type can be : Filter, Hashtag, Mention, Phrase, Word, Url.
 * Each token can have one or more attributes:  boost, include, exclude
 */

class QueryParseResult
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
     * An array containing tokens that was parsed
     *
     * @var array
     */
    protected $queryTokens = [];

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
     */
    public function parse($queryString)
    {
        $this->parser->readString($queryString, true);
        $this->queryTokens = $this->parser->parse();
        $this->parseFilters();
        $this->parseHashtags();
        $this->parseMentions();
        $this->parsePhrases();
        $this->parseWords();
        $this->parseUrls();
    }

    /**
     * Returns all filter token types
     *
     */
    private function parseFilters()
    {
        /** @var Node\ExplicitTerm[] */
        $this->filters = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_FILTER);
    }

    /**
     * Returns all hashtag token types
     *
     */
    private function parseHashtags()
    {
        /** @var Node\Hashtag[] */
        $this->hashtags = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_HASHTAG);
    }

    /**
     * Returns all mention token types
     *
     */
    private function parseMentions()
    {
        /** @var Node\Mention[] */
        $this->mentions = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_MENTION);
    }

    /**
     * Returns all phrases token types
     *
     */
    private function parsePhrases()
    {
        /** @var Node\Phrase[] */
        $this->phrases = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_PHRASE);
    }

    /**
     * Returns all word token types
     *
     */
    private function parseWords()
    {
        /** @var Node\Words[] */
        $this->words = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_WORD);
    }

    /**
     * Returns all url token types
     *
     */
    private function parseUrls()
    {
        /** @var Node\Url[] */
        $this->urls = $this->queryTokens->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_URL);
    }

    /**
     * @return Node\ExplicitTerm[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return Node\Hashtag[]
     */
    public function getHashtags()
    {
        return $this->hashtags;
    }


    /**
     * @return Node\Mention[]
     */
    public function getMentions()
    {
        return $this->mentions;
    }

    /**
     * @return Node\Phrase[]
     */
    public function getPhrases()
    {
        return $this->phrases;
    }

    /**
     * @return Node\Word[]
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @return Node\Url[]
     */
    public function getUrls()
    {
        return $this->urls;
    }


}
