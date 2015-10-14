<?php

namespace Gdbots\QueryParser\Base;

use Gdbots\QueryParser\Lexer as ParserLexer;

/**
 * Tokenize an expression
 */
class Lexer extends ParserLexer
{
    /**
     * Expression tokens
     *
     * @var int
     */
    const T_HASHTAG           = 10;  // #
    const T_REQUIRE           = 11;  // +
    const T_EXCLUDE           = 12;  // -
    const T_COLON             = 13; // :

    /**
     * Map the constant values with its token type
     *
     * @var int[]
     */
    protected $constTokens = [
        '#' => self::T_HASHTAG,
        '+' => self::T_REQUIRE,
        '-' => self::T_EXCLUDE,
        ':' => self::T_COLON
    ];
}
