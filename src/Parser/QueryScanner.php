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
    const T_FILTER              = 8; // ":", ":>", ":<" or ":!"
    const T_BOOST               = 9; // "^"
    const T_OR_OPERATOR         = 10; // "OR"
    const T_AND_OPERATOR        = 11; // "AND"
    const T_WSPC                = 12; // white-space
    const T_PHRASE              = 13; // text between two quotes (parentheses)
    const T_QUOTE               = 14; // double parentheses
    const T_ILLEGAL             = 15; // illegal character

    // Match basic emoticons
    const REGEX_EMOTICONS_BASIC = '/(?<=^|\s)(?:>:\-?\(|:\-?\)|:\'\(|:\-?\|:\-?\/|:\-?\(|:\-?\*|:\-?\||:o\)|:\-?o|=\-?\)|:\-?D|:\-?p|:\-?P|:\-?b|;\-?p|;\-?P|;\-?b|;\-?\))/';

    // Match UTF-8 emoticons
    const REGEX_EMOTICONS_UTF8 = '/([\x{2712}\x{2714}\x{2716}\x{271d}\x{2721}\x{2728}\x{2733}\x{2734}\x{2744}\x{2747}\x{274c}\x{274e}\x{2753}-\x{2755}\x{2757}\x{2763}\x{2764}\x{2795}-\x{2797}\x{27a1}\x{27b0}\x{27bf}\x{2934}\x{2935}\x{2b05}-\x{2b07}\x{2b1b}\x{2b1c}\x{2b50}\x{2b55}\x{3030}\x{303d}\x{1f004}\x{1f0cf}\x{1f170}\x{1f171}\x{1f17e}\x{1f17f}\x{1f18e}\x{1f191}-\x{1f19a}\x{1f201}\x{1f202}\x{1f21a}\x{1f22f}\x{1f232}-\x{1f23a}\x{1f250}\x{1f251}\x{1f300}-\x{1f321}\x{1f324}-\x{1f393}\x{1f396}\x{1f397}\x{1f399}-\x{1f39b}\x{1f39e}-\x{1f3f0}\x{1f3f3}-\x{1f3f5}\x{1f3f7}-\x{1f4fd}\x{1f4ff}-\x{1f53d}\x{1f549}-\x{1f54e}\x{1f550}-\x{1f567}\x{1f56f}\x{1f570}\x{1f573}-\x{1f579}\x{1f587}\x{1f58a}-\x{1f58d}\x{1f590}\x{1f595}\x{1f596}\x{1f5a5}\x{1f5a8}\x{1f5b1}\x{1f5b2}\x{1f5bc}\x{1f5c2}-\x{1f5c4}\x{1f5d1}-\x{1f5d3}\x{1f5dc}-\x{1f5de}\x{1f5e1}\x{1f5e3}\x{1f5ef}\x{1f5f3}\x{1f5fa}-\x{1f64f}\x{1f680}-\x{1f6c5}\x{1f6cb}-\x{1f6d0}\x{1f6e0}-\x{1f6e5}\x{1f6e9}\x{1f6eb}\x{1f6ec}\x{1f6f0}\x{1f6f3}\x{1f910}-\x{1f918}\x{1f980}-\x{1f984}\x{1f9c0}\x{3297}\x{3299}\x{a9}\x{ae}\x{203c}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21a9}\x{21aa}\x{231a}\x{231b}\x{2328}\x{2388}\x{23cf}\x{23e9}-\x{23f3}\x{23f8}-\x{23fa}\x{24c2}\x{25aa}\x{25ab}\x{25b6}\x{25c0}\x{25fb}-\x{25fe}\x{2600}-\x{2604}\x{260e}\x{2611}\x{2614}\x{2615}\x{2618}\x{261d}\x{2620}\x{2622}\x{2623}\x{2626}\x{262a}\x{262e}\x{262f}\x{2638}-\x{263a}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}\x{2666}\x{2668}\x{267b}\x{267f}\x{2692}-\x{2694}\x{2696}\x{2697}\x{2699}\x{269b}\x{269c}\x{26a0}\x{26a1}\x{26aa}\x{26ab}\x{26b0}\x{26b1}\x{26bd}\x{26be}\x{26c4}\x{26c5}\x{26c8}\x{26ce}\x{26cf}\x{26d1}\x{26d3}\x{26d4}\x{26e9}\x{26ea}\x{26f0}-\x{26f5}\x{26f7}-\x{26fa}\x{26fd}\x{2702}\x{2705}\x{2708}-\x{270d}\x{270f}]|\x{23}\x{20e3}|\x{2a}\x{20e3}|\x{30}\x{20e3}|\x{31}\x{20e3}|\x{32}\x{20e3}|\x{33}\x{20e3}|\x{34}\x{20e3}|\x{35}\x{20e3}|\x{36}\x{20e3}|\x{37}\x{20e3}|\x{38}\x{20e3}|\x{39}\x{20e3}|\x{1f1e6}[\x{1f1e8}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f2}\x{1f1f4}\x{1f1f6}-\x{1f1fa}\x{1f1fc}\x{1f1fd}\x{1f1ff}]|\x{1f1e7}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ef}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1e8}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ee}\x{1f1f0}-\x{1f1f5}\x{1f1f7}\x{1f1fa}-\x{1f1ff}]|\x{1f1e9}[\x{1f1ea}\x{1f1ec}\x{1f1ef}\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1ff}]|\x{1f1ea}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ed}\x{1f1f7}-\x{1f1fa}]|\x{1f1eb}[\x{1f1ee}-\x{1f1f0}\x{1f1f2}\x{1f1f4}\x{1f1f7}]|\x{1f1ec}[\x{1f1e6}\x{1f1e7}\x{1f1e9}-\x{1f1ee}\x{1f1f1}-\x{1f1f3}\x{1f1f5}-\x{1f1fa}\x{1f1fc}\x{1f1fe}]|\x{1f1ed}[\x{1f1f0}\x{1f1f2}\x{1f1f3}\x{1f1f7}\x{1f1f9}\x{1f1fa}]|\x{1f1ee}[\x{1f1e8}-\x{1f1ea}\x{1f1f1}-\x{1f1f4}\x{1f1f6}-\x{1f1f9}]|\x{1f1ef}[\x{1f1ea}\x{1f1f2}\x{1f1f4}\x{1f1f5}]|\x{1f1f0}[\x{1f1ea}\x{1f1ec}-\x{1f1ee}\x{1f1f2}\x{1f1f3}\x{1f1f5}\x{1f1f7}\x{1f1fc}\x{1f1fe}\x{1f1ff}]|\x{1f1f1}[\x{1f1e6}-\x{1f1e8}\x{1f1ee}\x{1f1f0}\x{1f1f7}-\x{1f1fb}\x{1f1fe}]|\x{1f1f2}[\x{1f1e6}\x{1f1e8}-\x{1f1ed}\x{1f1f0}-\x{1f1ff}]|\x{1f1f3}[\x{1f1e6}\x{1f1e8}\x{1f1ea}-\x{1f1ec}\x{1f1ee}\x{1f1f1}\x{1f1f4}\x{1f1f5}\x{1f1f7}\x{1f1fa}\x{1f1ff}]|\x{1f1f4}\x{1f1f2}|\x{1f1f5}[\x{1f1e6}\x{1f1ea}-\x{1f1ed}\x{1f1f0}-\x{1f1f3}\x{1f1f7}-\x{1f1f9}\x{1f1fc}\x{1f1fe}]|\x{1f1f6}\x{1f1e6}|\x{1f1f7}[\x{1f1ea}\x{1f1f4}\x{1f1f8}\x{1f1fa}\x{1f1fc}]|\x{1f1f8}[\x{1f1e6}-\x{1f1ea}\x{1f1ec}-\x{1f1f4}\x{1f1f7}-\x{1f1f9}\x{1f1fb}\x{1f1fd}-\x{1f1ff}]|\x{1f1f9}[\x{1f1e6}\x{1f1e8}\x{1f1e9}\x{1f1eb}-\x{1f1ed}\x{1f1ef}-\x{1f1f4}\x{1f1f7}\x{1f1f9}\x{1f1fb}\x{1f1fc}\x{1f1ff}]|\x{1f1fa}[\x{1f1e6}\x{1f1ec}\x{1f1f2}\x{1f1f8}\x{1f1fe}\x{1f1ff}]|\x{1f1fb}[\x{1f1e6}\x{1f1e8}\x{1f1ea}\x{1f1ec}\x{1f1ee}\x{1f1f3}\x{1f1fa}]|\x{1f1fc}[\x{1f1eb}\x{1f1f8}]|\x{1f1fd}\x{1f1f0}|\x{1f1fe}[\x{1f1ea}\x{1f1f9}]|\x{1f1ff}[\x{1f1e6}\x{1f1f2}\x{1f1fc}])/u';

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
    public static $typeStrings = array (
        self::T_EOI               => 'EOI',
        self::T_WORD              => 'WORD',
        self::T_OPEN_PARENTHESIS  => 'OPEN_PARENTHESIS',
        self::T_CLOSE_PARENTHESIS => 'CLOSE_PARENTHESIS',
        self::T_EXCLUDE           => 'EXCLUDE',
        self::T_INCLUDE           => 'INCLUDE',
        self::T_HASHTAG           => 'HASHTAG',
        self::T_MENTION           => 'MENTION',
        self::T_FILTER            => 'FILTER',
        self::T_BOOST             => 'BOOST',
        self::T_OR_OPERATOR       => 'OR_OPERATOR',
        self::T_AND_OPERATOR      => 'AND_OPERATOR',
        self::T_WSPC              => 'WHITESPACE',
        self::T_PHRASE            => 'PHRASE',
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

        // PHRASE matches every possible input between double brackets.
        // Double parentheses are part of the match.
        self::T_PHRASE => '/^(\"[^"]*\")(.*)/',

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
        self::T_EXCLUDE => '/^(\-)(.*)/',
        self::T_INCLUDE => '/^(\+)(.*)/',
        self::T_HASHTAG => '/^(\#)(.*)/',
        self::T_MENTION => '/^(\@)(.*)/',
        self::T_FILTER  => '/^(\:[\>|\<|\!]?)(.*)/',
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
        if (preg_match_all('/[^\s\"\'\#\@]+|(\#[^\#\s\)]*)|(\@[^\@\s\)]*)|\"([^\"]*)\"|\'([^\']*)\'/', $input, $matches)) {
            $input = '';
            foreach ($matches[0] as $key => $value) {
                if ($ignoreOperator) {
                    if ($value == 'AND') {
                        $value = 'OR';
                    }

                    $value = str_replace('(', '', $value);
                    $value = str_replace(')', '', $value);
                }

                // remove entities chat if invalid
                foreach ([self::T_HASHTAG, self::T_MENTION] as $regEx) {
                    if (preg_match($this->regEx[$regEx], $value, $m)) {
                        if (!preg_match('/^([\w\d\-\^_.])/', $m[2], $m1)) {
                            $value = substr($value, 1);
                        } else {
                            $value = substr($value, 0, 1).preg_replace('/[^\w\d\-\^_.]/', '', $value);
                        }
                    }
                }
                if (in_array($value, ['#', '@'])) {
                    $value = sprintf('"%s"', $value);
                }

                // add quotes to emoticons
                foreach ([self::REGEX_EMOTICONS_BASIC, self::REGEX_EMOTICONS_UTF8] as $regEx) {
                    if (preg_match($regEx, $value, $m)) {
                        $value = str_replace($m[0], sprintf('"%s"', $m[0]), $value);
                    }
                }

                if (empty($value)) {
                    $input .= ' ';

                    continue;
                }

                $input .= $value;

                if (preg_match_all('/(\()/', $value, $m)) {
                    $openParenthesis += count($m[0]);
                }
                if (preg_match_all('/(\))/', $value, $m)) {
                    if (preg_match($this->regEx[self::T_PHRASE], $value, $m1)) {
                        if (preg_match_all('/(\))/', str_replace($m1[1], '', $value), $m2)) {
                            $openParenthesis -= count($m2[0]);
                        }
                    } else {
                        $openParenthesis -= count($m[0]);
                    }
                }

                if (
                    isset($matches[0][$key+1]) &&
                    (
                        !in_array(substr($matches[0][$key+1], 0, 1), [':', '^', ')']) ||
                        preg_match(self::REGEX_EMOTICONS_BASIC, $matches[0][$key+1])
                    ) &&
                    (
                        !in_array(substr($value, -1), [':', '^']) ||
                        preg_match(self::REGEX_EMOTICONS_BASIC, $value)
                    )
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
        for (; $openParenthesis<0; $openParenthesis++) {
            $input = '('.$input;
        }
        for (; $openParenthesis>0; $openParenthesis--) {
            $input .= ')';
        }

        // removed duplicate charactors and spces
        $input = preg_replace('/\s+/', ' ', $input);
        $input = preg_replace('/(?>\-)\K\-*/', '', $input);
        $input = preg_replace('/(?>\+)\K\+*/', '', $input);
        $input = preg_replace('/(?>\#)\K\#*/', '', $input);
        $input = preg_replace('/(?>\@)\K\@*/', '', $input);
        $input = preg_replace('/(?>\^)\K\^*/', '', $input);
        $input = preg_replace('/(\()(\s?)(OR|AND)(\s?)/', '$1', $input);
        $input = preg_replace('/(\(\))(\s?)(OR|AND)(\s?)/', '', $input);
        $input = preg_replace('/(\()(\s)/', '$1', $input);
        $input = preg_replace('/(\s)(\))/', '$1', $input);

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

        return self::$typeStrings[$tokenType];
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
