<?php

namespace Gdbots\QueryParser;

/**
 * Parses a query and returns a ParseResult object with a flat set of
 * buckets per token type, i.e. words, phrases, hashtags, etc.
 * The subquery aspects that are tokenized are ignored by this parser.
 */
class SimpleParser
{
    /** @var Tokenizer */
    private $tokenizer;

    /**
     * Constructs a new SimpleParser.
     */
    public function __construct()
    {
        $this->tokenizer = new Tokenizer();
    }

    /**
     * @param string $input
     * @return array
     */
    public function parse($input)
    {
        $this->tokenizer->scan($input);
        $this->tokenizer->moveNext();

        while (null !== $this->tokenizer->lookahead()) {
            if (isset($this->tokenizer->lookahead()['type'])) {
                echo str_pad($this->tokenizer->getTypeName($this->tokenizer->lookahead()['type']), 19, ' ') .
                    ($this->tokenizer->lookahead()['value'] ? ' => ' . $this->tokenizer->lookahead()['value'] : '') . PHP_EOL;
            }

            $this->tokenizer->moveNext();
        }

        return $this->tokenizer->getTokens();
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param integer $token Type of token.
     *
     * @return boolean True if tokens match; false otherwise.
     */
    private function match($token)
    {
        if (!$this->tokenizer->isNextToken($token)) {
            $this->syntaxError($this->tokenizer->getTypeName($token));
        }

        return $this->tokenizer->moveNext();
    }

    /**
     * Attempts to match the current lookahead token with any of the given tokens.
     *
     * If any of them matches, this method updates the lookahead token; otherwise
     * a syntax error is raised.
     *
     * @param array $tokens
     *
     * @return boolean
     */
    private function matchAny(array $tokens)
    {
        if (!$this->tokenizer->isNextTokenAny($tokens)) {
            $this->syntaxError(implode(' or ', array_map([$this->tokenizer, 'getTypeName'], $tokens)));
        }

        return $this->tokenizer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string     $expected Expected string.
     * @param array|null $token    Optional token.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->tokenizer->lookahead();
        }

        $message  = sprintf('Expected %s, got ', $expected);
        $message .= ($this->tokenizer->lookahead() === null)
            ? 'end of string'
            : sprintf("'%s' at position %s", $token['value'], $token['position']);

        $message .= '.';

        throw new \InvalidArgumentException($message);
    }
}
