<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Parser;
use Gdbots\QueryParser\Grammar;
use Gdbots\QueryParser\Lexer;
use Gdbots\QueryParser\Base\Grammar as BaseGrammar;
use Gdbots\QueryParser\Base\Lexer as BaseLexer;

/**
 * Class used to simplify final developer usage
 */
class SearchQueryParser
{
    /**
     * Lexer used to evaluate the query
     *
     * @var Lexer
     */
    private $lexer;

    /**
     * The query to parse
     *
     * @var string
     */
    private $query = null;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * The stream to parse
     *
     * @var TokenStream
     */
    private $stream;

    /**
     * The class constructor
     *
     * @param string $query Optional search query
     */
    public function __construct($query = null)
    {
        $this->setLexer(new BaseLexer());
        $this->parser = new Parser(new BaseGrammar());
        $this->setQuery($query);
    }

    /**
     * Sets the lexer
     *
     * @param Lexer $lexer
     */
    public function setLexer(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Sets the grammar
     *
     * @param Grammar $grammar
     */
    public function setGrammar(Grammar $grammar)
    {
        $this->parser = new Parser($grammar);
    }

    /**
     * Sets the search query
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Gets the search query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Elaboration output
     *
     * @return mixed
     */
    public function evaluate()
    {
        $this->stream = $this->lexer->scan($this->query);

        return $this->parser->parse($this->stream)->evaluate();
    }
}
