<?php

namespace Gdbots\QueryParser\Parser;

/**
 * A simple scanner for search queries. The scanner also functions as tokenizer, returning
 * only tokens and matched strings, instead of feeding character per character. The scanner
 * utilizes PHP built-in regular expressions to match tokens by order of priority. The
 * scanner needs to be steered manually with the "next" function to make it scan the next
 * token.
 *
 * Whitespace tokens are treated as separator between two semantic tokens and are
 * automatically discarded. Following classic tokenizers, tokens are represented by their:
 * - token type, in form of an integer constant. Technically, PHP can work fine with string
 *   representations for token types, but in this scanner, integers are used and a function
 *   is provided to convert the integer token type to textual representation.
 * - token content, in the form of a string.
 *
 * For debugging and error reporting reasons, the scanner retains all input to be processed,
 * all input that is processed and the position of the scanner in the original input string.
 */
class QueryScanner
{
    const T_EOI                 = 0; // end of input
    const T_WORD                = 1; // word
    const T_OPEN_PARENTHESIS    = 2; // "("
    const T_CLOSE_PARENTHESIS   = 3; // ")"
    const T_EXCLUDE             = 4; // "-"
    const T_INCLUDE             = 5; // "+"
    const T_HASHTAG             = 6; // "#"
    const T_MENTION             = 7; // "@"
    const T_COLON               = 8; // ":"
    const T_BOOST               = 9; // "^"
    const T_OR_OPERATOR         = 10; // "OR"
    const T_AND_OPERATOR        = 11; // "AND"
    const T_WSPC                = 12; // white-space
    const T_TEXT                = 13; // text between two quotes (parentheses)
    const T_QUOTE               = 14; // double parentheses
    const T_ILLEGAL             = 15; // illegal character

    /**
     * The input string which has already been processed and data back into tokens.
     *
     * @var string
     */
    private $processed;

    /**
     * The input string to be processed. This is shortened as it is processed.
     *
     * @var string
     */
    private $input;

    /**
     * The position of the scanner relative to the original input string.
     *
     * @var int
     */
    private $position;

    /**
     * The last text / token that processed the scanner.
     *
     * @var string
     */
    private $token;

    /**
     * The type of token which has processed the scanner , denoted by the constant.
     *
     * @var int
     */
    private $tokenType;

    /**
     * The textual representation of the token types.
     *
     * @var array
     */
    private $typeStrings = array (
        self::T_EOI               => 'EOI',
        self::T_WORD              => 'WORD',
        self::T_OPEN_PARENTHESIS  => 'OPEN_PARENTHESIS',
        self::T_CLOSE_PARENTHESIS => 'CLOSE_PARENTHESIS',
        self::T_EXCLUDE           => 'EXCLUDE',
        self::T_INCLUDE           => 'INCLUDE',
        self::T_HASHTAG           => 'HASHTAG',
        self::T_MENTION           => 'MENTION',
        self::T_COLON             => 'COLON',
        self::T_BOOST             => 'BOOST',
        self::T_OR_OPERATOR       => 'OR_OPERATOR',
        self::T_AND_OPERATOR      => 'AND_OPERATOR',
        self::T_WSPC              => 'WHITESPACE',
        self::T_TEXT              => 'TEXT',
        self::T_QUOTE             => 'QUOTE',
        self::T_ILLEGAL           => 'ILLEGAL'
    );

    /**
     * The regular expressions per token type their token type matches the input.
     * This expression must contain two sub-expressions: the first for the characters
     * that match the token scanned itself, the second for the remaining characters
     * in the string (normally "(*)." To record all the remaining characters.
     *
     * By not only to describe the characters -Well-match we have more control over
     * which characters do not have to match. For example, for keywords like "OR"
     * we can impose here that after "OR" a space or any non-word character should be.
     *
     * The order of the regular expressions are determinable firing order in which
     * the tokens will be matched. This is important in adjusting or adding expressions.
     *
     * For example, keywords will always have to come in front of the word token, or
     * the keyword will be considered as a word.
     *
     * Inspection of an illegal nature should always be the last, if no other expression matches.
     * The whitespace is best expressed in the first place.
     *
     * @var array
     */
    private $regEx = [

        // WSPC matches in (frequent) spaces, tabs and newlines.
        self::T_WSPC => '/^([ \t\n]+)(.*)/',

        // TEXT matches every possible input between double brackets.
        // Double parentheses are part of the match.
        self::T_TEXT => '/^(\"[^"]*\")(.*)/',

        // OR matches by keyword "OR" (case sensitive)
        // when no text follows after "OR".
        self::T_OR_OPERATOR => '/^(OR)(\b.*)/',

        // AND matches by keyword "AND" (case sensitive)
        // when no text follows after "AND".
        self::T_AND_OPERATOR => '/^(AND)(\b.*)/',

        // WORD matches letters, numbers, underscores, hyphens and
        // points (think eg. To dibe_relict.101) Can not match up
        // truncation characters and accents, which should be
        // encapsulated in quotes.
        self::T_WORD => '/^([\w\d_][\w\d\/_\-.]*)(.*)/',

        // parentheses
        self::T_OPEN_PARENTHESIS => '/^(\()(.*)/',
        self::T_CLOSE_PARENTHESIS => '/^(\))(.*)/',

        // hyphen, colon, quote
        self::T_EXCLUDE => '/^(-)(.*)/',
        self::T_INCLUDE => '/^(\+)(.*)/',
        self::T_HASHTAG => '/^(\#)(.*)/',
        self::T_MENTION => '/^(@)(.*)/',
        self::T_COLON   => '/^(:)(.*)/',
        self::T_BOOST   => '/^(\^)(.*)/',
        self::T_QUOTE   => '/^(\")([^"]*)$/',

        // this should match with each character that is left over.
        self::T_ILLEGAL => '/^(.)(.*)/'
    ];

    /**
     * Displays the part of the input string that's already been processed.
     *
     * @return string
     */
    public function getProcessedData()
    {
        return $this->processed;
    }

    /**
     * Indicates the part of the input string remaining to be processed.
     *
     * @return string
     */
    public function getRemainingData()
    {
        return $this->input;
    }

    /**
     * Returns the position of the scanner in the original input string.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Reads the new input string and set the position to 0.
     *
     * @param string $input
     * @param bool   $ignoreOperator
     */
    public function readString($input, $ignoreOperator = false)
    {
        $openParenthesis = 0;

        // find all strings and rebuild input string with "OR"
        if (preg_match_all('/[^\\s\"\']+|\"([^\"]*)\"|\'([^\']*)\'/', $input, $matches)) {
            $input = '';
            foreach ($matches[0] as $key => $value) {
                if ($ignoreOperator) {
                    if ($value == 'AND') {
                        $value = 'OR';
                    }

                    $value = str_replace('(', '', $value);
                    $value = str_replace(')', '', $value);
                }

                $input .= $value;

                if (preg_match_all('/(\()/', $value, $m)) {
                    $openParenthesis += count($m[0]);
                }
                if (preg_match_all('/(\))/', $value, $m)) {
                    $openParenthesis -= count($m[0]);
                }

                if (
                    isset($matches[0][$key+1]) &&
                    !in_array(substr($matches[0][$key+1], 0, 1), [':', '^', ')']) &&
                    !in_array(substr($value, -1), [':', '^'])
                ) {
                    if (
                        !in_array($matches[0][$key+1], ['AND', 'OR']) &&
                        !in_array($value, ['AND', 'OR', '('])
                    ) {
                        $input .= ' OR ';
                    } else {
                        $input .= ' ';
                    }
                }
            }
        }

        // add missing close parentheses
        for (; $openParenthesis>0; $openParenthesis--) {
            $input .= ')';
        }

        // removed duplicate spaces
        $input = preg_replace('/\s+/', ' ', $input);

        $this->input = $input;
        $this->processed = '';
        $this->position = 0;
    }

    /**
     * Return the current token type.
     *
     * @return int
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Returns a textual verion of the token type:
     * - the token type (constant) if given
     * - the last processed token if no parameter is passed
     *
     * @param int $tokenType
     *
     * @return string
     */
    public function getTokenTypeText($tokenType = null)
    {
        if ($tokenType == null) {
            $tokenType = $this->tokenType;
        }

        return $this->typeStrings[$tokenType];
    }

    /**
     * Returns the current token.
     *
     * @return int
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Processes the all tokens and indicates the current type.
     *
     * @return int
     */
    public function next()
    {
        // test each token type
        foreach ($this->regEx as $tokenType => $reg) {
            if ($this->testToken($reg, $tokenType) && $this->getTokenType() != self::T_WSPC) {
                return $this->getTokenType();
            }
        }

        // if no token matches, we are probably at the end. The control is
        // still entered, the "preg_match" expression failure for illegal
        // characters.
        if ($this->input != '') {
            $this->tokenType = self::T_ILLEGAL;
            $this->token = $this->input;
            $this->input = '';
            return self::T_ILLEGAL;
        }

        $this->tokenType = self::T_EOI;
        $this->token = null;

        return self::T_EOI;
    }

    /**
     * Test an expression for a match and process as token.
     *
     * @param string $regEx
     * @param int    $tokenType
     *
     * @return bool
     */
    private function testToken($regEx, $tokenType)
    {
        if (preg_match($regEx, $this->input, $matches)) {
            $this->token = $matches[1];
            $this->processed .= $matches[1];
            $this->input = $matches[2];
            $this->tokenType = $tokenType;
            $this->position = $this->position + strlen($this->token);

            return true;
        }

        return false;
    }
}
