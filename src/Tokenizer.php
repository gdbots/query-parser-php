<?php
declare(strict_types=1);

namespace Gdbots\QueryParser;

final class Tokenizer
{
    const REGEX_EMOTICON = '(?<=^|\s)(?:>:\-?\(|:\-?\)|<3|:\'\(|:\-?\|:\-?\/|:\-?\(|:\-?\*|:\-?\||:o\)|:\-?o|=\-?\)|:\-?D|:\-?p|:\-?P|:\-?b|;\-?p|;\-?P|;\-?b|;\-?\))';
    const REGEX_EMOJI = '[\x{2712}\x{2714}\x{2716}\x{271d}\x{2721}\x{2728}\x{2733}\x{2734}\x{2744}\x{2747}\x{274c}\x{274e}\x{2753}-\x{2755}\x{2757}\x{2763}\x{2764}\x{2795}-\x{2797}\x{27a1}\x{27b0}\x{27bf}\x{2934}\x{2935}\x{2b05}-\x{2b07}\x{2b1b}\x{2b1c}\x{2b50}\x{2b55}\x{3030}\x{303d}\x{1f004}\x{1f0cf}\x{1f170}\x{1f171}\x{1f17e}\x{1f17f}\x{1f18e}\x{1f191}-\x{1f19a}\x{1f201}\x{1f202}\x{1f21a}\x{1f22f}\x{1f232}-\x{1f23a}\x{1f250}\x{1f251}\x{1f300}-\x{1f321}\x{1f324}-\x{1f393}\x{1f396}\x{1f397}\x{1f399}-\x{1f39b}\x{1f39e}-\x{1f3f0}\x{1f3f3}-\x{1f3f5}\x{1f3f7}-\x{1f4fd}\x{1f4ff}-\x{1f53d}\x{1f549}-\x{1f54e}\x{1f550}-\x{1f567}\x{1f56f}\x{1f570}\x{1f573}-\x{1f579}\x{1f587}\x{1f58a}-\x{1f58d}\x{1f590}\x{1f595}\x{1f596}\x{1f5a5}\x{1f5a8}\x{1f5b1}\x{1f5b2}\x{1f5bc}\x{1f5c2}-\x{1f5c4}\x{1f5d1}-\x{1f5d3}\x{1f5dc}-\x{1f5de}\x{1f5e1}\x{1f5e3}\x{1f5ef}\x{1f5f3}\x{1f5fa}-\x{1f64f}\x{1f680}-\x{1f6c5}\x{1f6cb}-\x{1f6d0}\x{1f6e0}-\x{1f6e5}\x{1f6e9}\x{1f6eb}\x{1f6ec}\x{1f6f0}\x{1f6f3}\x{1f910}-\x{1f918}\x{1f980}-\x{1f984}\x{1f9c0}\x{3297}\x{3299}\x{a9}\x{ae}\x{203c}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21a9}\x{21aa}\x{231a}\x{231b}\x{2328}\x{2388}\x{23cf}\x{23e9}-\x{23f3}\x{23f8}-\x{23fa}\x{24c2}\x{25aa}\x{25ab}\x{25b6}\x{25c0}\x{25fb}-\x{25fe}\x{2600}-\x{2604}\x{260e}\x{2611}\x{2614}\x{2615}\x{2618}\x{261d}\x{2620}\x{2622}\x{2623}\x{2626}\x{262a}\x{262e}\x{262f}\x{2638}-\x{263a}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}\x{2666}\x{2668}\x{267b}\x{267f}\x{2692}-\x{2694}\x{2696}\x{2697}\x{2699}\x{269b}\x{269c}\x{26a0}\x{26a1}\x{26aa}\x{26ab}\x{26b0}\x{26b1}\x{26bd}\x{26be}\x{26c4}\x{26c5}\x{26c8}\x{26ce}\x{26cf}\x{26d1}\x{26d3}\x{26d4}\x{26e9}\x{26ea}\x{26f0}-\x{26f5}\x{26f7}-\x{26fa}\x{26fd}\x{2702}\x{2705}\x{2708}-\x{270d}\x{270f}]|\x{23}\x{20e3}|\x{2a}\x{20e3}|\x{30}\x{20e3}|\x{31}\x{20e3}|\x{32}\x{20e3}|\x{33}\x{20e3}|\x{34}\x{20e3}|\x{35}\x{20e3}|\x{36}\x{20e3}|\x{37}\x{20e3}|\x{38}\x{20e3}|\x{39}\x{20e3}|\x{1f1e6}[\x{1f1e8}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f2}\x{1f1f4}\x{1f1f6}-\x{1f1fa}\x{1f1fc}\x{1f1fd}\x{1f1ff}]|\x{1f1e7}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ef}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1e8}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ee}\x{1f1f0}-\x{1f1f5}\x{1f1f7}\x{1f1fa}-\x{1f1ff}]|\x{1f1e9}[\x{1f1ea}\x{1f1ec}\x{1f1ef}\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1ff}]|\x{1f1ea}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ed}\x{1f1f7}-\x{1f1fa}]|\x{1f1eb}[\x{1f1ee}-\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1f7}]|\x{1f1ec}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ee}\x{1f1f1}-\x{1f1f3}\x{1f1f5}-\x{1f1fa}\x{1f1fc}\x{1f1fe}]|\x{1f1ed}[\x{1f1f0}\x{1f1f2}\x{1f1f3}\x{1f1f7}\x{1f1f9}\x{1f1fa}]|\x{1f1ee}[\x{1f1e8}-\x{1f1ea}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}]|\x{1f1ef}[\x{1f1ea}\x{1f1f2}\x{1f1f4}\x{1f1f5}]|\x{1f1f0}[\x{1f1ea}\x{1f1ec}-\x{1f1ee}\x{1f1f2}\x{1f1f3}\x{1f1f5}\x{1f1f7}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1f1}[\x{1f1e6}-\x{1f1e8}\x{1f1ee}\x{1f1f0}\x{1f1f7}-\x{1f1fb}\x{1f1fe}]|\x{1f1f2}[\x{1f1e6}\x{1f1e8}-\x{1f1ed}\x{1f1f0}-\x{1f1ff}]|\x{1f1f3}[\x{1f1e6}\x{1f1e8}\x{1f1ea}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f4}\x{1f1f5}\x{1f1f7}\x{1f1fa}\x{1f1ff}]|\x{1f1f4}\x{1f1f2}|\x{1f1f5}[\x{1f1e6}\x{1f1ea}-\x{1f1ed}\x{1f1f0}-\x{1f1f3}\x{1f1f7}-\x{1f1f9}\x{1f1fc}\x{1f1fe}]|\x{1f1f6}\x{1f1e6}|\x{1f1f7}[\x{1f1ea}\x{1f1f4}\x{1f1f8}\x{1f1fa}\x{1f1fc}]|\x{1f1f8}[\x{1f1e6}-\x{1f1ea}\x{1f1ec}-\x{1f1f4}\x{1f1f7}-\x{1f1f9}\x{1f1fb}\x{1f1fd}-\x{1f1ff}]|\x{1f1f9}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ed}\x{1f1ef}-\x{1f1f4}\x{1f1f7}\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1ff}]|\x{1f1fa}[\x{1f1e6}\x{1f1ec}\x{1f1f2}\x{1f1f8}\x{1f1fe}\x{1f1ff}]|\x{1f1fb}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ee}\x{1f1f3}\x{1f1fa}]|\x{1f1fc}[\x{1f1eb}\x{1f1f8}]|\x{1f1fd}\x{1f1f0}|\x{1f1fe}[\x{1f1ea}\x{1f1f9}]|\x{1f1ff}[\x{1f1e6}\x{1f1f2}\x{1f1fc}]';
    const REGEX_URL = '[+-]?[\w-]+:\/\/[^\s\/$.?#].[^\s\^~]*';
    const REGEX_PHRASE = '[+-]?"(?:""|[^"])*"';
    const REGEX_HASHTAG = '[+-]?#+[a-zA-Z0-9_]+';
    const REGEX_MENTION = '[+-]?@+[a-zA-Z0-9_]+(?:[a-zA-Z0-9_\.\-]+)?';
    const REGEX_NUMBER = '(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?';
    const REGEX_DATE = '[+-]?\d{4}-\d{2}-\d{2}';
    const REGEX_FIELD = '[+-]?[a-zA-Z\_]+(?:[a-zA-Z0-9_\.\-]+)?:';
    const REGEX_WORD = '[+-]?[^\s\(\)\\\\^\<\>\[\]\{\}~=]*';
    const REGEX_WORD_MINIMUM = '[a-zA-Z0-9\pL]+';
    const IGNORED_LEAD_TRAIL_CHARS = "#@,.!?;|&+-^~*\\\"' \t\n\r ";

    /**
     * When building a field lexeme we switch this on/off to establish proper T_FIELD_END.
     * It also helps us enforce range and subquery rules.
     *
     * @var bool
     */
    private bool $inField = false;

    /**
     * This tokenizer only supports one level of sub query (for now).  We only want to take
     * a query from a user like "funny #cats plays:>500" and parse that to a simple
     * object which can be translated to a sql, elasticsearch, riak, etc. query.
     *
     * @var bool
     */
    private bool $inSubquery = false;

    /**
     * This tokenizer only supports one range to be open at a time (excl or incl).
     * Starting a new range of any type is ignored if it's already open and
     * closing a range that never started is also ignored.
     *
     * The value will be the type of range that is open or 0.
     *
     * @var int
     */
    private int $inRange = 0;

    /**
     * The regex used to split the initial input into chunks that will be
     * checked for tokens during scan/tokenization.
     *
     * @var string
     */
    private string $splitRegex;

    /** @var Token[] */
    private array $tokens = [];

    /**
     * The last token that was scanned.
     *
     * @var Token
     */
    private Token $lastToken;

    public function __construct()
    {
        $this->splitRegex = sprintf(
            '/(%s)/iu',
            implode(')|(', [
                self::REGEX_EMOTICON,
                self::REGEX_URL,
                self::REGEX_PHRASE,
                self::REGEX_FIELD,
                self::REGEX_WORD,
            ])
        );
        $this->lastToken = new Token(Token::T_WHITE_SPACE);
    }

    /**
     * The Tokenizer is immediately reset and the new input tokenized.
     * Any unprocessed tokens from any previous input are lost.
     *
     * @param string $input
     *
     * @return TokenStream
     */
    public function scan(string $input): TokenStream
    {
        $input = str_replace('""', '" "', preg_replace('/\s+/', ' ', ' ' . $input));
        // $input = substr($input, 0, 256); // lef
        $this->inField = false;
        $this->inSubquery = false;
        $this->inRange = 0;
        $this->tokens = [];
        $this->lastToken = new Token(Token::T_WHITE_SPACE);

        foreach ($this->splitInput($input) as $match) {
            $this->extractTokens(trim($match[0]));

            if ($this->lastToken->isWhiteSpace() && $this->inField && !$this->inRange && !$this->inSubquery) {
                $this->inField = false;
                $this->addOperatorToken(Token::T_FIELD_END);
            }
        }

        if ($this->inField) {
            $this->inField = false;
            $this->addOperatorToken(Token::T_FIELD_END);
        }

        if ($this->inSubquery) {
            $this->inSubquery = false;
            $this->addOperatorToken(Token::T_SUBQUERY_END);
        }

        $this->tokens = array_values(array_filter($this->tokens, function (Token $token) {
            return !$token->isWhiteSpace() && !$token->isIgnored();
        }));

        return new TokenStream($this->tokens);
    }

    /**
     * Splits the input into chunks that will be scanned for tokens.
     *
     * @param string $input
     *
     * @return array
     */
    private function splitInput(string $input): array
    {
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        return preg_split($this->splitRegex, $input, -1, $flags);
    }

    /**
     * Adds an operator token (tokens with no value).  This method
     * also ensures the same token is not repeated.
     *
     * @param int $type
     */
    private function addOperatorToken(int $type): void
    {
        if ($this->lastToken->typeEquals($type)) {
            return;
        }

        $token = new Token($type);
        $this->tokens[] = $token;
        $this->lastToken = $token;
    }

    private function addToken(int $type, float|string|null $value): void
    {
        $token = new Token($type, $value);
        $this->tokens[] = $token;
        $this->lastToken = $token;
    }

    private function extractTokens(string $value): void
    {
        if ('' === $value) {
            if ($this->lastToken->typeEqualsAnyOf([Token::T_REQUIRED, Token::T_PROHIBITED, Token::T_IGNORED])) {
                // todo: review the process of bool operators following ignored values.
                array_pop($this->tokens);
            }
            $this->addOperatorToken(Token::T_WHITE_SPACE);
            return;
        }

        if (is_numeric($value)) {
            $this->addToken(Token::T_NUMBER, (float)$value);
            return;
        }

        if ($this->extractSymbolOrKeyword($value)) {
            return;
        }

        switch ($value[0]) {
            case '+':
                $this->addOperatorToken(Token::T_REQUIRED);
                $value = substr($value, 1);
                break;

            case '-':
                $this->addOperatorToken(Token::T_PROHIBITED);
                $value = substr($value, 1);
                break;

            default:
                break;
        }

        if (preg_match('/^' . self::REGEX_EMOTICON . '$/', $value)) {
            $this->addToken(Token::T_EMOTICON, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^' . self::REGEX_EMOJI . '$/u', $value)) {
            $this->addToken(Token::T_EMOJI, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^' . self::REGEX_URL . '$/', $value)) {
            $this->addToken(Token::T_URL, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (!$this->inField && !$this->inSubquery
            && preg_match('/^' . self::REGEX_FIELD . '$/', $value)
            && $this->lastToken->typeEqualsAnyOf([
                Token::T_WHITE_SPACE,
                Token::T_REQUIRED,
                Token::T_PROHIBITED,
                Token::T_FIELD_END,
                Token::T_SUBQUERY_START,
            ])
        ) {
            $this->inField = true;
            $this->addToken(Token::T_FIELD_START, rtrim($value, ':'));
            return;
        }

        if (preg_match('/^' . self::REGEX_PHRASE . '$/', $value)) {
            $value = trim(trim($value, '"'));
            if (!empty($value)) {
                $this->addToken(Token::T_PHRASE, $value);
            } else {
                $this->addToken(Token::T_IGNORED, $value);
            }
            return;
        }

        if (str_contains($value, '..')) {
            $parts = explode('..', $value, 2);
            $this->extractTokens($parts[0]);
            $this->extractSymbolOrKeyword('..');
            $this->extractTokens($parts[1] ?? '');
            return;
        }

        if (preg_match('/^' . self::REGEX_HASHTAG . '$/', rtrim($value, self::IGNORED_LEAD_TRAIL_CHARS))) {
            $this->addToken(Token::T_HASHTAG, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^' . self::REGEX_MENTION . '$/', rtrim($value, self::IGNORED_LEAD_TRAIL_CHARS))) {
            $this->addToken(Token::T_MENTION, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/^' . self::REGEX_DATE . '$/', rtrim($value, self::IGNORED_LEAD_TRAIL_CHARS))) {
            $this->addToken(Token::T_DATE, trim($value, self::IGNORED_LEAD_TRAIL_CHARS));
            return;
        }

        if (preg_match('/' . self::REGEX_WORD . '/', $value)) {
            $hasTrailingWildcard = str_ends_with($value, '*');
            $value2 = trim($value, self::IGNORED_LEAD_TRAIL_CHARS . '/');
            if (!empty($value2)) {
                /*
                 * When in a field or subquery you can get a value which itself looks like the start
                 * of a field, e.g. "field:vevo:video".  We don't want two words here so
                 * merge the last "word" token value with this one.
                 */
                if ($this->lastToken->typeEquals(Token::T_WORD)
                    && ':' === strrev($this->lastToken->getValue())[0]
                ) {
                    $value2 = array_pop($this->tokens)->getValue() . $value2;
                }

                if (!preg_match('/' . self::REGEX_WORD_MINIMUM . '/u', $value2)) {
                    $this->addToken(Token::T_IGNORED, $value2);
                    return;
                }

                $this->addToken(Token::T_WORD, $value2);

                if ($hasTrailingWildcard) {
                    $this->addOperatorToken(Token::T_WILDCARD);
                }

                return;
            }
        }

        $this->addToken(Token::T_IGNORED, $value);
    }

    /**
     * Extracts a symbol or keyword from the string and may ignore a token
     * if it doesn't follow some basic rules for this lib.  E.g. you can't
     * boost whitespace " ^5".  In that case, boost is ignored.
     *
     * @param string $value
     *
     * @return bool True if a symbol or keyword was extracted/processed.
     */
    private function extractSymbolOrKeyword(string $value): bool
    {
        $len = strlen($value);
        if ($len > 3) {
            return false;
        }

        switch ($value) {
            case '+':
                $this->addOperatorToken(Token::T_REQUIRED);
                return true;

            case '-':
                $this->addOperatorToken(Token::T_PROHIBITED);
                return true;

            case '>':
                if ($this->inField && 0 === $this->inRange) {
                    $this->addOperatorToken(Token::T_GREATER_THAN);
                }
                return true;

            case '<':
                if ($this->inField && 0 === $this->inRange) {
                    $this->addOperatorToken(Token::T_LESS_THAN);
                }
                return true;

            case '=':
                if ($this->lastToken->typeEquals(Token::T_GREATER_THAN)
                    || $this->lastToken->typeEquals(Token::T_LESS_THAN)
                ) {
                    $this->addOperatorToken(Token::T_EQUALS);
                }
                return true;

            case '~':
                // can't fuzzy parts of a field, range or sub query
                if ($this->inSubquery || 0 !== $this->inRange) {
                    // fuzzy is ignored
                    return true;
                }

                if (!$this->lastToken->isWhiteSpace()) {
                    if ($this->inField) {
                        $this->inField = false;
                        $this->addOperatorToken(Token::T_FIELD_END);
                    }
                    $this->addOperatorToken(Token::T_FUZZY);
                }
                return true;

            case '^':
                // can't boost parts of a field, range or sub query
                if ($this->inSubquery || 0 !== $this->inRange) {
                    // boost is ignored
                    return true;
                }

                if (!$this->lastToken->isWhiteSpace()) {
                    if ($this->inField) {
                        $this->inField = false;
                        $this->addOperatorToken(Token::T_FIELD_END);
                    }
                    $this->addOperatorToken(Token::T_BOOST);
                }
                return true;

            case '[':
                if ($this->inField && 0 === $this->inRange) {
                    $this->inRange = Token::T_RANGE_INCL_START;
                    $this->addOperatorToken(Token::T_RANGE_INCL_START);
                }
                return true;

            case '{':
                if ($this->inField && 0 === $this->inRange) {
                    $this->inRange = Token::T_RANGE_EXCL_START;
                    $this->addOperatorToken(Token::T_RANGE_EXCL_START);
                }
                return true;

            case ']':
            case '}':
                if (0 !== $this->inRange) {
                    if (Token::T_RANGE_INCL_START === $this->inRange) {
                        $this->addOperatorToken(Token::T_RANGE_INCL_END);
                    } else {
                        $this->addOperatorToken(Token::T_RANGE_EXCL_END);
                    }

                    $this->inRange = 0;
                    $this->inField = false;
                    $this->addOperatorToken(Token::T_FIELD_END);
                }
                return true;

            case '(':
                // sub queries can't be nested or exist in a range.
                if (!$this->inSubquery && 0 === $this->inRange) {
                    $this->addOperatorToken(Token::T_SUBQUERY_START);
                    $this->inSubquery = true;
                }
                return true;

            case ')':
                if ($this->inSubquery && 0 === $this->inRange) {
                    $this->inSubquery = false;
                    $this->addOperatorToken(Token::T_SUBQUERY_END);

                    if ($this->inField) {
                        $this->addOperatorToken(Token::T_FIELD_END);
                        $this->inField = false;
                    }
                }
                return true;

            case '*':
                $this->addOperatorToken(Token::T_WILDCARD);
                return true;

            case '||':
            case 'OR':
                $this->addOperatorToken(Token::T_OR);
                return true;

            case '&&':
            case 'AND':
                $this->addOperatorToken(Token::T_AND);
                return true;

            case '..':
                if (0 !== $this->inRange) {
                    $this->addOperatorToken(Token::T_TO);
                }
                return true;

            case 'TO':
                if (0 !== $this->inRange) {
                    $this->addOperatorToken(Token::T_TO);
                    return true;
                }

                $this->addToken(Token::T_WORD, $value);
                return true;

            default:
                if (1 === $len) {
                    if (ctype_alpha($value)) {
                        /*
                         * A word, followed ":", followed by a single char "thing:a".
                         * can be made into one token.
                         * todo: review words that look like fields.  seems wonky.
                         */
                        if ($this->lastToken->typeEquals(Token::T_WORD)
                            && ':' === strrev($this->lastToken->getValue())[0]
                        ) {
                            $value = array_pop($this->tokens)->getValue() . $value;
                        }

                        $this->addToken(Token::T_WORD, $value);
                        return true;
                    }

                    $this->addToken(Token::T_IGNORED, $value);
                    return true;
                }
                break;
        }

        return false;
    }
}
