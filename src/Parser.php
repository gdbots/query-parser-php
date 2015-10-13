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
     * Create a new token
     * @param string $queryString
     */
    public function __construct($queryString) {
        $this->queryString = $queryString;
    }

    /**
     * Parses search query into tokens
     *
     * @return array
     */

    public function parse(){
        $tokenArray = Lexer::tokenize($this->queryString);
        return $tokenArray;
    }

}
