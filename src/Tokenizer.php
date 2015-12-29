<?php

namespace Gdbots\QueryParser;

class Tokenizer
{
    // All tokens that are not valid identifiers must be < 100
    const T_WHITE_SPACE      = 0;
    const T_NUMBER           = 1;  // 10, 0.8, .64, 6.022e23
    const T_REQUIRED         = 2;  // '+'
    const T_PROHIBITED       = 3;  // '-'
    const T_GREATER_THAN     = 4;  // '>'
    const T_LESS_THAN        = 5;  // '<'
    const T_EQUALS           = 6;  // '='
    const T_FUZZY            = 7;  // '~'
    const T_BOOST            = 8;  // '^'
    const T_RANGE_INCL_START = 9;  // '['
    const T_RANGE_INCL_END   = 10; // ']'
    const T_RANGE_EXCL_START = 11; // '{'
    const T_RANGE_EXCL_END   = 12; // '}'
    const T_SUBQUERY_START   = 13; // '('
    const T_SUBQUERY_END     = 14; // ')'
    const T_WILDCARD         = 15; // '*'

    // All tokens that are identifiers should be >= 100
    const T_IGNORED  = 100;  // an ignored token, e.g. #, !, etc.  when found by themselves, don't do anything with them.
    const T_WORD     = 101;
    const T_FIELD    = 102; // field:value
    const T_PHRASE   = 103; // Phrase (one or more quoted words)
    const T_URL      = 104; // a valid url
    const T_DATE     = 105; // date in the format YYYY-MM-DD
    const T_HASHTAG  = 106; // #hashtag
    const T_MENTION  = 107; // @mention
    const T_EMOTICON = 108; // see https://en.wikipedia.org/wiki/Emoticon
    const T_EMOJI    = 109; // see https://en.wikipedia.org/wiki/Emoji

    // All keyword tokens should be >= 200
    const T_AND = 200; // 'AND' or '&&'
    const T_OR  = 201; // 'OR' or '||'
    const T_TO  = 202; // 'TO' or '..'

    const IGNORED_LEAD_TRAIL_CHARS = "#@,.!?|&+-^~*\\\"' \t\n\r ";

    const REGEX_EMOTICON = '(?<=^|\s)(?:>:\-?\(|:\-?\)|<3|:\'\(|:\-?\|:\-?\/|:\-?\(|:\-?\*|:\-?\||:o\)|:\-?o|=\-?\)|:\-?D|:\-?p|:\-?P|:\-?b|;\-?p|;\-?P|;\-?b|;\-?\))';
    const REGEX_EMOJI    = '[\x{2712}\x{2714}\x{2716}\x{271d}\x{2721}\x{2728}\x{2733}\x{2734}\x{2744}\x{2747}\x{274c}\x{274e}\x{2753}-\x{2755}\x{2757}\x{2763}\x{2764}\x{2795}-\x{2797}\x{27a1}\x{27b0}\x{27bf}\x{2934}\x{2935}\x{2b05}-\x{2b07}\x{2b1b}\x{2b1c}\x{2b50}\x{2b55}\x{3030}\x{303d}\x{1f004}\x{1f0cf}\x{1f170}\x{1f171}\x{1f17e}\x{1f17f}\x{1f18e}\x{1f191}-\x{1f19a}\x{1f201}\x{1f202}\x{1f21a}\x{1f22f}\x{1f232}-\x{1f23a}\x{1f250}\x{1f251}\x{1f300}-\x{1f321}\x{1f324}-\x{1f393}\x{1f396}\x{1f397}\x{1f399}-\x{1f39b}\x{1f39e}-\x{1f3f0}\x{1f3f3}-\x{1f3f5}\x{1f3f7}-\x{1f4fd}\x{1f4ff}-\x{1f53d}\x{1f549}-\x{1f54e}\x{1f550}-\x{1f567}\x{1f56f}\x{1f570}\x{1f573}-\x{1f579}\x{1f587}\x{1f58a}-\x{1f58d}\x{1f590}\x{1f595}\x{1f596}\x{1f5a5}\x{1f5a8}\x{1f5b1}\x{1f5b2}\x{1f5bc}\x{1f5c2}-\x{1f5c4}\x{1f5d1}-\x{1f5d3}\x{1f5dc}-\x{1f5de}\x{1f5e1}\x{1f5e3}\x{1f5ef}\x{1f5f3}\x{1f5fa}-\x{1f64f}\x{1f680}-\x{1f6c5}\x{1f6cb}-\x{1f6d0}\x{1f6e0}-\x{1f6e5}\x{1f6e9}\x{1f6eb}\x{1f6ec}\x{1f6f0}\x{1f6f3}\x{1f910}-\x{1f918}\x{1f980}-\x{1f984}\x{1f9c0}\x{3297}\x{3299}\x{a9}\x{ae}\x{203c}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21a9}\x{21aa}\x{231a}\x{231b}\x{2328}\x{2388}\x{23cf}\x{23e9}-\x{23f3}\x{23f8}-\x{23fa}\x{24c2}\x{25aa}\x{25ab}\x{25b6}\x{25c0}\x{25fb}-\x{25fe}\x{2600}-\x{2604}\x{260e}\x{2611}\x{2614}\x{2615}\x{2618}\x{261d}\x{2620}\x{2622}\x{2623}\x{2626}\x{262a}\x{262e}\x{262f}\x{2638}-\x{263a}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}\x{2666}\x{2668}\x{267b}\x{267f}\x{2692}-\x{2694}\x{2696}\x{2697}\x{2699}\x{269b}\x{269c}\x{26a0}\x{26a1}\x{26aa}\x{26ab}\x{26b0}\x{26b1}\x{26bd}\x{26be}\x{26c4}\x{26c5}\x{26c8}\x{26ce}\x{26cf}\x{26d1}\x{26d3}\x{26d4}\x{26e9}\x{26ea}\x{26f0}-\x{26f5}\x{26f7}-\x{26fa}\x{26fd}\x{2702}\x{2705}\x{2708}-\x{270d}\x{270f}]|\x{23}\x{20e3}|\x{2a}\x{20e3}|\x{30}\x{20e3}|\x{31}\x{20e3}|\x{32}\x{20e3}|\x{33}\x{20e3}|\x{34}\x{20e3}|\x{35}\x{20e3}|\x{36}\x{20e3}|\x{37}\x{20e3}|\x{38}\x{20e3}|\x{39}\x{20e3}|\x{1f1e6}[\x{1f1e8}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f2}\x{1f1f4}\x{1f1f6}-\x{1f1fa}\x{1f1fc}\x{1f1fd}\x{1f1ff}]|\x{1f1e7}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ef}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1e8}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ee}\x{1f1f0}-\x{1f1f5}\x{1f1f7}\x{1f1fa}-\x{1f1ff}]|\x{1f1e9}[\x{1f1ea}\x{1f1ec}\x{1f1ef}\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1ff}]|\x{1f1ea}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ed}\x{1f1f7}-\x{1f1fa}]|\x{1f1eb}[\x{1f1ee}-\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1f7}]|\x{1f1ec}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ee}\x{1f1f1}-\x{1f1f3}\x{1f1f5}-\x{1f1fa}\x{1f1fc}\x{1f1fe}]|\x{1f1ed}[\x{1f1f0}\x{1f1f2}\x{1f1f3}\x{1f1f7}\x{1f1f9}\x{1f1fa}]|\x{1f1ee}[\x{1f1e8}-\x{1f1ea}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}]|\x{1f1ef}[\x{1f1ea}\x{1f1f2}\x{1f1f4}\x{1f1f5}]|\x{1f1f0}[\x{1f1ea}\x{1f1ec}-\x{1f1ee}\x{1f1f2}\x{1f1f3}\x{1f1f5}\x{1f1f7}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1f1}[\x{1f1e6}-\x{1f1e8}\x{1f1ee}\x{1f1f0}\x{1f1f7}-\x{1f1fb}\x{1f1fe}]|\x{1f1f2}[\x{1f1e6}\x{1f1e8}-\x{1f1ed}\x{1f1f0}-\x{1f1ff}]|\x{1f1f3}[\x{1f1e6}\x{1f1e8}\x{1f1ea}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f4}\x{1f1f5}\x{1f1f7}\x{1f1fa}\x{1f1ff}]|\x{1f1f4}\x{1f1f2}|\x{1f1f5}[\x{1f1e6}\x{1f1ea}-\x{1f1ed}\x{1f1f0}-\x{1f1f3}\x{1f1f7}-\x{1f1f9}\x{1f1fc}\x{1f1fe}]|\x{1f1f6}\x{1f1e6}|\x{1f1f7}[\x{1f1ea}\x{1f1f4}\x{1f1f8}\x{1f1fa}\x{1f1fc}]|\x{1f1f8}[\x{1f1e6}-\x{1f1ea}\x{1f1ec}-\x{1f1f4}\x{1f1f7}-\x{1f1f9}\x{1f1fb}\x{1f1fd}-\x{1f1ff}]|\x{1f1f9}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ed}\x{1f1ef}-\x{1f1f4}\x{1f1f7}\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1ff}]|\x{1f1fa}[\x{1f1e6}\x{1f1ec}\x{1f1f2}\x{1f1f8}\x{1f1fe}\x{1f1ff}]|\x{1f1fb}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ee}\x{1f1f3}\x{1f1fa}]|\x{1f1fc}[\x{1f1eb}\x{1f1f8}]|\x{1f1fd}\x{1f1f0}|\x{1f1fe}[\x{1f1ea}\x{1f1f9}]|\x{1f1ff}[\x{1f1e6}\x{1f1f2}\x{1f1fc}]';
    const REGEX_URL      = '[+-]?[\w-]+:\/\/[^\s\/$.?#].[^\s\^~]*';
    const REGEX_PHRASE   = '[+-]?"(?:""|[^"])*"';
    const REGEX_HASHTAG  = '[+-]?#[a-zA-Z0-9_]+';
    const REGEX_MENTION  = '[+-]?@[a-zA-Z0-9_]+(?:[a-zA-Z0-9_\.\-]+)?';
    const REGEX_NUMBER   = '(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?';
    const REGEX_FIELD    = '[+-]?[a-zA-Z\_]+(?:[a-zA-Z0-9_\.\-]+)?:';
    const REGEX_WORD     = '[+-]?[^\s\(\)\\\\^\<\>\[\]\{\}~=]*';

    /**
     * Array of the type names on this class.
     * @var array
     */
    private static $typeNames;

    /**
     * The regex used to split the initial input into chunks that will be
     * checked for tokens during scan/tokenization.
     *
     * @var string
     */
    private $splitRegex;

    /**
     * The original input string.
     *
     * @var string
     */
    private $input;

    /**
     * Array of scanned tokens.
     *
     * Each token is an associative array containing two items:
     *  - 'type'  : the type of the token (int) (whitespace, word, phrase, etc.)
     *  - 'value' : the string value of the token in the input string
     *
     * @var array
     */
    private $tokens = [];

    /**
     * The last token that was scanned.
     *
     * @var array
     */
    private $lastScannedToken;

    /**
     * Current tokenizer position in input string.
     *
     * @var int
     */
    private $position = 0;

    /**
     * Current peek of current tokenizer position.
     *
     * @var int
     */
    private $peek = 0;

    /**
     * The next token in the input.
     *
     * @var array
     */
    private $lookahead;

    /**
     * The last matched/seen token.
     *
     * @var array
     */
    private $token;

    /**
     * The Tokenizer is immediately reset and the new input tokenized.
     * Any unprocessed tokens from any previous input are lost.
     *
     * @param string $input The input to be tokenized.
     *
     * @return self
     */
    public function scan($input)
    {
        $input = preg_replace('/\s+/', ' ', ' '.$input);
        $this->input  = $input;
        $this->tokens = [];
        $this->lastScannedToken = ['type' => self::T_WHITE_SPACE, 'value' => null];

        $this->reset();

        foreach ($this->splitInput($this->input) as $match) {
            $this->extractTokens(trim($match[0]));
        }

        return $this;
    }

    /**
     * Resets the tokenizer.
     *
     * @return self
     */
    public function reset()
    {
        $this->lookahead = null;
        $this->token = null;
        $this->peek = 0;
        $this->position = 0;
        return $this;
    }

    /**
     * Resets the peek pointer to 0.
     *
     * @return self
     */
    public function resetPeek()
    {
        $this->peek = 0;
        return $this;
    }

    /**
     * Checks whether a given token type matches the current lookahead.
     *
     * @param int $type
     *
     * @return bool
     */
    public function isNextToken($type)
    {
        return null !== $this->lookahead && $this->lookahead['type'] === $type;
    }

    /**
     * Checks whether any of the given tokens matches the current lookahead.
     *
     * @param array $tokens
     *
     * @return bool
     */
    public function isNextTokenAny(array $tokens)
    {
        return null !== $this->lookahead && in_array($this->lookahead['type'], $tokens, true);
    }

    /**
     * Moves to the next token in the input string.
     *
     * @return bool
     */
    public function moveNext()
    {
        $this->peek = 0;
        $this->token = $this->lookahead;
        $this->lookahead = (isset($this->tokens[$this->position]))
            ? $this->tokens[$this->position++] : null;

        return $this->lookahead !== null;
    }

    /**
     * Tells the tokenizer to skip input tokens until it sees a token with the given value.
     *
     * @param int $type The token type to skip until.
     *
     * @return self
     */
    public function skipUntil($type)
    {
        while ($this->lookahead !== null && $this->lookahead['type'] !== $type) {
            $this->moveNext();
        }

        return $this;
    }

    /**
     * Moves the lookahead token forward.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function peek()
    {
        if (isset($this->tokens[$this->position + $this->peek])) {
            return $this->tokens[$this->position + $this->peek++];
        } else {
            return null;
        }
    }

    /**
     * Peeks at the next token, returns it and immediately resets the peek.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function glimpse()
    {
        $peek = $this->peek();
        $this->peek = 0;
        return $peek;
    }

    /**
     * Returns the next token in the input.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function lookahead()
    {
        return $this->lookahead;
    }

    /**
     * Returns the last matched/seen token.
     *
     * @return array|null The current token or NULL
     */
    public function currentToken()
    {
        return $this->token;
        //return $this->token ?: ['type' => -1, 'value' => null];
    }

    /**
     * Returns all tokens extracted from the input string.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Gets the name of the type (a T_BLAH constant) by its integer value.
     *
     * @param int $type
     *
     * @return string
     */
    public static function getTypeName($type)
    {
        if (null === static::$typeNames) {
            static::$typeNames = [];

            foreach ((new \ReflectionClass(get_called_class()))->getConstants() as $name => $value) {
                if (0 === strpos($name, 'T_')) {
                    static::$typeNames[$value] = $name;
                }
            }
        }

        return isset(static::$typeNames[$type]) ? static::$typeNames[$type] : $type;
    }

    /**
     * Splits the input into chunks that will be scanned for tokens.
     *
     * @param string $input
     * @return array The array returned from preg_split
     */
    private function splitInput($input)
    {
        if (null === $this->splitRegex) {
            $this->splitRegex = sprintf(
                '/(%s)/iu',
                implode(')|(', [
                    self::REGEX_EMOTICON,
                    self::REGEX_URL,
                    self::REGEX_PHRASE,
                    self::REGEX_FIELD,
                    self::REGEX_WORD
                ])
            );
        }

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        return preg_split($this->splitRegex, $input, -1, $flags);
    }

    /**
     * @param int $type
     * @param string $value
     *
     * @return self
     */
    private function addToken($type, $value = null)
    {
        $token = ['type' => (int) $type, 'value' => $value];
        $this->tokens[] = $token;
        $this->lastScannedToken = $token;
        return $this;
    }

    /**
     * @param string $value
     */
    private function extractTokens($value)
    {
        if ('' === $value) {
            if (self::T_WHITE_SPACE !== $this->lastScannedToken['type']) {
                $this->addToken(self::T_WHITE_SPACE);
            }
            return;
        }

        if (is_numeric($value)) {
            $this->addToken(self::T_NUMBER, (float)$value);
            return;
        }

        if ($this->extractSymbolOrKeyword($value)) {
            return;
        }

        switch ($value[0]) {
            case '+':
                $this->addToken(self::T_REQUIRED);
                $value = substr($value, 1);
                break;

            case '-':
                $this->addToken(self::T_PROHIBITED);
                $value = substr($value, 1);
                break;

            default:
                break;
        }

        if (preg_match('/^'.self::REGEX_EMOTICON.'$/', $value)) {
            $this->addToken(self::T_EMOTICON, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^'.self::REGEX_EMOJI.'$/u', $value)) {
            $this->addToken(self::T_EMOJI, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^'.self::REGEX_URL.'$/', $value)) {
            $this->addToken(self::T_URL, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^'.self::REGEX_FIELD.'$/', $value)
            && in_array($this->lastScannedToken['type'], [self::T_WHITE_SPACE, self::T_REQUIRED, self::T_PROHIBITED])
        ) {
            $this->addToken(self::T_FIELD, trim($value, ':'));
            return;
        }

        if (preg_match('/^'.self::REGEX_PHRASE.'$/', $value)) {
            $this->addToken(self::T_PHRASE, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (false !== strpos($value, '..')) {
            $parts = explode('..', $value, 2);
            $this->extractTokens($parts[0]);
            $this->addToken(self::T_TO);
            $this->extractTokens(isset($parts[1]) ? $parts[1] : '');
            return;
        }

        if (preg_match('/^'.self::REGEX_HASHTAG.'$/', $value)) {
            $this->addToken(self::T_HASHTAG, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^'.self::REGEX_MENTION.'$/', $value)) {
            $this->addToken(self::T_MENTION, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/'.self::REGEX_WORD.'/', $value)) {
            $hasTrailingWildcard = '*' === substr($value, -1);
            $value2 = trim($value, self::IGNORED_LEAD_TRAIL_CHARS.'/');
            if (!empty($value2)) {
                $this->addToken(self::T_WORD, $value2);
                if ($hasTrailingWildcard) {
                    $this->addToken(self::T_WILDCARD);
                }

                return;
            }

            $this->addToken(self::T_IGNORED, $value);
        }
    }

    /**
     * @param string $value
     * @return bool True if a symbol or keyword was extracted.
     */
    private function extractSymbolOrKeyword($value)
    {
        $len = strlen($value);
        if ($len > 3) {
            return false;
        }

        switch (strtoupper($value)) {
            case '+': $this->addToken(self::T_REQUIRED); return true;
            case '-': $this->addToken(self::T_PROHIBITED); return true;
            case '>': $this->addToken(self::T_GREATER_THAN); return true;
            case '<': $this->addToken(self::T_LESS_THAN); return true;
            case '=': $this->addToken(self::T_EQUALS); return true;
            case '~': $this->addToken(self::T_FUZZY); return true;
            case '^': $this->addToken(self::T_BOOST); return true;
            case '[': $this->addToken(self::T_RANGE_INCL_START); return true;
            case ']': $this->addToken(self::T_RANGE_INCL_END); return true;
            case '{': $this->addToken(self::T_RANGE_EXCL_START); return true;
            case '}': $this->addToken(self::T_RANGE_EXCL_END); return true;
            case '(': $this->addToken(self::T_SUBQUERY_START); return true;
            case ')': $this->addToken(self::T_SUBQUERY_END); return true;
            case '*': $this->addToken(self::T_WILDCARD); return true;

            case '||':
            case 'OR':
                $this->addToken(self::T_OR);
                return true;

            case '&&':
            case 'AND':
                $this->addToken(self::T_AND);
                return true;

            case '..':
            case 'TO':
                $this->addToken(self::T_TO);
                return true;

            default:
                if (1 === $len) {
                    if (ctype_alpha($value)) {
                        $this->addToken(self::T_WORD, $value);
                        return true;
                    }

                    $this->addToken(self::T_IGNORED, $value);
                    return true;
                }
                break;
        }

        return false;
    }
}
