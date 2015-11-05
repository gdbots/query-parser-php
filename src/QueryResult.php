<?php

namespace Gdbots\QueryParser;

/**
 * Takes a query string and returns an object with different classified token types.
 * The type can be : Filter, Hashtag, Mention, Phrase, Word, Url.
 * Each token can have one or more attributes:  boost, include, exclude
 */
class QueryResult
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
     * An array containing words that was parsed
     *
     * @var array
     */
    protected $words = [];

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
    protected $urls = [];

    /**
     * An array containing dates that was parsed
     *
     * @var array
     */
    protected $dates = [];

    /**
     * An array containing numbers that was parsed
     *
     * @var array
     */
    protected $numbers = [];

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
     * An array containing filters that was parsed
     *
     * @var array
     */
    protected $filters = [];

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

        if ($this->queryItem = $parser->parse($inputString, $ignoreOperator)) {
            $items = $this->queryItem->getQueryItemsByTokenType();
            foreach ($items as $tokenType => $values) {
                $property = strtolower($tokenType).'s';

                if (property_exists($this, $property)) {
                    $this->$property = $values;
                }
            }
        }

        $this->inputString = $inputString;
        $this->compiledString = $parser->getLexer()->getProcessedData();

        return $items;
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
    public function getQueryItem()
    {
        return $this->queryItem;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Phrase[]
     */
    public function getPhrases()
    {
        return $this->phrases;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Word[]
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Hashtag[]
     */
    public function getHashtags()
    {
        return $this->hashtags;
    }

    /**
     * @return \Gdbots\QueryParser\Node\Mention[]
     */
    public function getMentions()
    {
        return $this->mentions;
    }

    /**
     * @return \Gdbots\QueryParser\Node\ExplicitTerm[]
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
