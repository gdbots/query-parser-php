<?php
namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Exception\QueryParserException;
use Gdbots\QueryParser\Lexer;

class Parser
{
    /**
     * Search Query string
     * @var string
     */
    protected $queryString;

    /**
     * Normalized Query string
     * @var string
     */
    protected $normalizedQueryString;

    /**
     * Create a new token
     * @param string $queryString
     */
    public function __construct($queryString) {
        $this->queryString = $queryString;
        $this->setNormalizedQuery();
    }

    /**
     * Parses search query into tokens
     *
     * @return array
     */

    public function parse(){
        $tokenArray = Lexer::tokenize($this->normalizedQueryString);
        return $tokenArray;
    }

    private function setnormalizedQuery(){
        $this->normalizedQueryString = preg_replace('/[\/\[\]\(\)\{\}\?\\\]/', ' ', $this->queryString);
        $this->normalizedQueryString = preg_replace('/(\s+:\s+)/', ':', $this->normalizedQueryString);
        $this->normalizedQueryString = preg_replace('/\s+/', ' ', $this->normalizedQueryString);
        $this->normalizedQueryString = str_replace(':<', ':&lt;', $this->normalizedQueryString);
        $this->normalizedQueryString = strip_tags($this->normalizedQueryString);
        $this->normalizedQueryString = html_entity_decode($this->normalizedQueryString, ENT_COMPAT, 'utf-8');

        if (empty($this->normalizedQueryString)) {
            throw new QueryParserException('Search was empty after removing whitespace and html.');
        }

        $this->normalizedQueryString = str_ireplace([' and ', ' or '], [' AND ', ' OR '], $this->normalizedQueryString);
    }

}
