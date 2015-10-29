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
     * The string to parse
     *
     * @var string
     */
    protected $inputString;

    /**
     * The parsed string
     *
     * @var string
     */
    protected $compiledString;

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
     * Parses a given query string and assign all tokenized buckets.
     *
     * @param string $queryString
     * @param bool   $ignoreOperator
     *
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem
     */
    public function parse($inputString, $ignoreOperator = true)
    {
        $parser = new QueryParser();
        $this->queryItem = $parser->parse($inputString, $ignoreOperator);

        $this->inputString = $inputString;
        $this->compiledString = $parser->getLexer()->getProcessedData();

        $items = $this->queryItem->getQueryItemsByTokenType();
        foreach ($items as $tokenType => $items) {
            $property = strtolower($tokenType).'s';

            if (property_exists($this, $property)) {
                $this->$property = $items;
            }
        }

        return $this->getParseResultQueryItem();
    }

    /**
     * @return string
     */
    public function getInputString()
    {
        return $this->inputString;
    }

    /**
     * @return string
     */
    public function getCompiledString()
    {
        return $this->compiledString;
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
