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
 *
 * @todo Refactor EOL token to EOF (end of file) token, or EOI (end of input).
 *       The EOL token is erronously used by the scanner to denote the end of the
 *       input string.
 */
class QueryScanner
{
    /**
     * Constant for "End of Line" tokens. An "end of line" indicates the end of the inputs.
     */
    const T_EOL = 0;

    /**
     * Constant for a " Word" token.
     */
    const T_WORD = 1;

    /**
     * Stands for "(", "Left paren" token.
     */
    const T_LPAREN = 2;

    /**
     * Stands for ")", " right pairs " token.
     */
    const T_RPAREN = 3;

    /**
     * Stands for "-", a "minus" token.
     */
    const T_MINUS = 5;

    /**
     * Stands for "#", a "hashtag" token.
     */
    const T_HASHTAG = 6;

    /**
     * Stands for "@", a "Mention" token.
     */
    const T_MENTION = 7;

    /**
     *  Stands for ":", a "colon"  token.
     */
    const T_COLON = 8;

    /**
     *  Stands for "^", a "boost"  token.
     */
    const T_BOOST = 9;

    /**
     * Stands for "OR", if word. A "OR operator" token.
     */
    const T_OR_OPERATOR = 10;

    /**
     * Stands for "AND", if word. A "AND operator" token.
     */
    const T_AND_OPERATOR = 11;

    /**
     * Stands for every kind (and amount) of spaces, a "White Space" token.
     */
    const T_WSPC = 12;

    /**
     * A "TEXT" token represents text between two quotes (parentheses).
     * This is already the scanner captured from simplicity for both
     * scanner and parser and in order to avoid a complete parse of the text.
     */
    const T_TEXT = 13;

    /**
     * '"' A quote token represents double parentheses.
     * Because the scanner automatically all text between double
     * parenthesis in a TEXT token move, this ratio is only
     * returned for a double hook without matching
     * double closing parenthesis.
     */
    const T_QUOTE = 14;

    /**
     * An illegal character, such as, a control character or character system.
     * This should not occur.
     */
    const T_ILLEGAL = 15;

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
        self::T_EOL          => 'EOL',
        self::T_WORD         => 'WORD',
        self::T_LPAREN       => 'LPAREN',
        self::T_RPAREN       => 'RPAREN',
        self::T_MINUS        => 'MINUS',
        self::T_HASHTAG      => 'HASHTAG',
        self::T_MENTION      => 'MENTION',
        self::T_COLON        => 'COLON',
        self::T_BOOST        => 'BOOST',
        self::T_OR_OPERATOR  => 'OR_OPERATOR',
        self::T_AND_OPERATOR => 'AND_OPERATOR',
        self::T_WSPC         => 'WHITESPACE',
        self::T_TEXT         => 'TEXT',
        self::T_QUOTE        => 'QUOTE',
        self::T_ILLEGAL      => 'ILLEGAL'
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
    private $regEx = array(
        // WSPC matches in (frequent) spaces, tabs and newlines.
        self::T_WSPC => '#^([ \t\n]+)(.*)#',

        // TEXT matches every possible input between double brackets.
        // Double parentheses are part of the match.
        self::T_TEXT => '#^(\"[^"]*\")(.*)#',

        // OR matches by keyword "OR" (case sensitive)
        // when no text follows after "OR".
        self::T_OR_OPERATOR => '#^(OR)(\b.*)#',

        // AND matches by keyword "AND" (case sensitive)
        // when no text follows after "AND".
        self::T_AND_OPERATOR => '#^(AND)(\b.*)#',

        // WORD matches letters, numbers, underscores, hyphens and
        // points (think eg. To dibe_relict.101) Can not match up
        // truncation characters and accents, which should be
        // encapsulated in quotes.
        self::T_WORD => '#^([\w\d_][\w\d\/_\-.]*)(.*)#',

        // parens, brackets
        self::T_LPAREN => '#^(\()(.*)#',
        self::T_RPAREN => '#^(\))(.*)#',

        // hyphen, colon, quote
        self::T_MINUS   => '#^(-)(.*)#',
        self::T_HASHTAG => '#^(\#)(.*)#',
        self::T_MENTION => '#^(@)(.*)#',
        self::T_COLON   => '#^(:)(.*)#',
        self::T_BOOST   => '#^(\^)(.*)#',
        self::T_QUOTE   => '#^(\")([^"]*)$#',

        // this should match with each character that is left over.
        self::T_ILLEGAL => '#^(.)(.*)#'
    );

    /**
     * Displays the part of the input string back that's already been processed.
     *
     * @return string
     */
    public function getProcessedData()
    {
        return $this->processed;
    }

    /**
     * Indicates the part of the input string returned yet to be processed.
     *
     * @return string
     */
    public function getRemainingData()
    {
        return $this->input;
    }

    /**
     * Returns the position of the scanner in the original input string back.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Reads the new input string and set the position to 0. The processed data will be cleared.
     *
     * @param string $input
     */
    public function readString($input)
    {
        // file all strings and rebuild input string with "OR"
        if (preg_match_all('/[^\\s\"\']+|\"([^\"]*)\"|\'([^\']*)\'/', $input, $matches)) {
            $input = '';
            foreach ($matches[0] as $key => $value) {
                $input .= $value;

                if (
                    isset($matches[0][$key+1]) &&
                    $matches[0][$key+1] != 'AND' &&
                    $matches[0][$key+1] != 'OR' &&
                    $value != 'AND' &&
                    $value != 'OR' &&
                    $value != '(' &&
                    substr($value, -1) != ':'
                ) {
                    $input .= ' OR ';
                } else {
                    $input .= ' ';
                }
            }
        }

        // removed duplicate spaces
        $input = preg_replace('/\s+/', ' ', $input);

        // removed spaces around operators
        $input = preg_replace('/(\ ?)([!|:|=|<|>])(\ ?)/', '$2', $input);

        $this->input = $input;
        $this->processed = "";
        $this->position = 0;
    }

    /**
     * Gives the token type (constant) back from the last processed token.
     *
     * @return int
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * The textual name of the token type back:
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
     * Gives the token (text) back from the last processed token.
     *
     * @return int
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Processes the following token and indicates the type of back .
     *
     * @return int
     */
    public function next()
    {
        // test for each token type in turn
        foreach ($this->regEx as $tokenType => $reg) {
            if ($this->testToken($reg, $tokenType) &&($this->getTokenType() != self::T_WSPC)) {
                return $this->getTokenType();
            }
        }

        // if no token matches, we are probably at the end. The control is
        // still entered, was the "match all" expression failure for illegal
        // characters.
        if ($this->input != "") {
            $this->tokenType = self::T_ILLEGAL;
            $this->token = $this->input;
            $this->input = "";
            return self::T_ILLEGAL;
        }

        $this->tokenType = self::T_EOL;
        $this->token = null;

        return self::T_EOL;
    }

    /**
     * Auxiliary Function to test an expression for a match and process as token.
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
