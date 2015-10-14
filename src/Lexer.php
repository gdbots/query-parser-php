<?php

namespace Gdbots\QueryParser;

/**
 * Tokenize an expression
 */
class Lexer
{
    /**
     * Expression tokens
     *
     * @var int
     */
    const T_NONE              = 0;
    const T_OPEN_PARENTHESIS  = 1;  // (
    const T_CLOSE_PARENTHESIS = 2;  // )
    const T_NUMBER            = 3;  // 1,0.1
    const T_STRING            = 4;  // a..zA..Z,;.:-_...
    const T_AND               = 5;  // &&
    const T_OR                = 6;  // ||
    const T_NOT               = 7;  // !

    /**
     * The lexemes to find the tokens
     *
     * @var array
     */
    protected $lexemes = [
        '([\d]+\.?[\d]*)',
        '([\w\d\']+)',
        '(\"[^"]*\")',
        '(.)'
    ];

    /**
     * Map the constant values with its token type
     *
     * @var int[]
     */
    protected $_constTokens = [
        '(' => self::T_OPEN_PARENTHESIS,
        ')' => self::T_CLOSE_PARENTHESIS,
        'and' => self::T_AND,
        '&&' => self::T_AND,
        'or' => self::T_OR,
        '||' => self::T_OR,
        '!' => self::T_NOT,
        'not' => self::T_NOT
    ];

    /**
     * Adds a new lexem to the lexemes array
     *
     * @param string $lexem
     */
    public function addLexem($lexem)
    {
        $this->lexemes[] = $lexem;
    }

    /**
     * Returns the instance of the lexemes array
     *
     * @return array
     */
    public function getLexemes()
    {
        return $this->lexemes;
    }

    /**
     * Tokenize the given input string and return the resulting token stream
     *
     * @param string $input The string input to scan
     *
     * @return TokenStream The resulting token stream
     */
    public function scan($input)
    {
        $stream = $this->tokenize($input);
        $stream->rewind();

        return $stream;
    }

    /**
     * Transform the input string into a token stream
     *
     * @param string $input The string input to tokenize
     *
     * @return TokenStream The resulting token stream
     */
    protected function tokenize($input)
    {
        $stream = new TokenStream();
        $stream->setSource($input);

        // PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
        $matches = preg_split('/' . implode('|', $this->getLexemes()) . '/', $input, -1, 7);

        foreach ($matches as $match) {
            $value = strtolower($match[0]);

            if (is_numeric($value)) {
                $code = self::T_NUMBER;
            } elseif (isset($this->_constTokens[$value])) {
                $code = $this->_constTokens[$value];
            } elseif (isset($this->constTokens[$value])) {
                $code = $this->constTokens[$value];
            } elseif (ctype_space($value)) {
                continue;
            } elseif (is_string($value)) {
                $code = self::T_STRING;
            } else {
                $code = self::T_NONE;
            }

            $stream->push(new Token($code, $match[0], $match[1]));
        }

        return $stream;
    }
}
