<?php

namespace Gdbots\QueryParser;

class Token implements \JsonSerializable
{
    const T_EOI              = 0;  // end of input
    const T_WHITE_SPACE      = 1;
    const T_IGNORED          = 2;  // an ignored token, e.g. #, !, etc.  when found by themselves, don't do anything with them.
    const T_NUMBER           = 3;  // 10, 0.8, .64, 6.022e23
    const T_REQUIRED         = 4;  // '+'
    const T_PROHIBITED       = 5;  // '-'
    const T_GREATER_THAN     = 6;  // '>'
    const T_LESS_THAN        = 7;  // '<'
    const T_EQUALS           = 8;  // '='
    const T_FUZZY            = 9;  // '~'
    const T_BOOST            = 10; // '^'
    const T_RANGE_INCL_START = 11; // '['
    const T_RANGE_INCL_END   = 12; // ']'
    const T_RANGE_EXCL_START = 13; // '{'
    const T_RANGE_EXCL_END   = 14; // '}'
    const T_SUBQUERY_START   = 15; // '('
    const T_SUBQUERY_END     = 16; // ')'
    const T_WILDCARD         = 17; // '*'
    const T_AND              = 18; // 'AND' or '&&'
    const T_OR               = 19; // 'OR' or '||'
    const T_TO               = 20; // 'TO' or '..'
    const T_WORD             = 21;
    const T_FILTER_START     = 22; // The "field:" portion of "field:value".
    const T_FILTER_END       = 23; // when a filter lexeme ends, i.e. "field:value". This token has no value.
    const T_PHRASE           = 24; // Phrase (one or more quoted words)
    const T_URL              = 25; // a valid url
    const T_DATE             = 26; // date in the format YYYY-MM-DD
    const T_HASHTAG          = 27; // #hashtag
    const T_MENTION          = 28; // @mention
    const T_EMOTICON         = 29; // see https://en.wikipedia.org/wiki/Emoticon
    const T_EMOJI            = 30; // see https://en.wikipedia.org/wiki/Emoji

    /**
     * Array of the type names by id (constants flipped)
     *
     * @var array
     */
    private static $typeNames;

    /** @var int */
    private $type = self::T_EOI;

    /** @var string|float|null */
    private $value;

    /**
     * @param int $type
     * @param string|float|null $value
     */
    public function __construct($type, $value = null)
    {
        $this->type = (int)$type;
        $this->value = $value;
    }

    /**
     * Gets the name of the type (a T_FOO constant) by its integer value.
     *
     * @param int $type
     *
     * @return string
     */
    public static function name($type)
    {
        if (null === self::$typeNames) {
            static::$typeNames = array_flip((new \ReflectionClass(__CLASS__))->getConstants());
        }

        return isset(self::$typeNames[$type]) ? self::$typeNames[$type] : $type;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return ['type' => $this->type, 'value' => $this->value];
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return self::name($this->type);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|float|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    public function typeEquals($type)
    {
        return (int)$type === $this->type;
    }

    /**
     * @param int[] $types
     *
     * @return bool
     */
    public function typeEqualsAnyOf(array $types)
    {
        return in_array($this->type, $types, true);
    }

    /**
     * @return bool
     */
    public function isWhiteSpace()
    {
        return self::T_WHITE_SPACE === $this->type;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return self::T_IGNORED === $this->type;
    }

    /**
     * @return bool
     */
    public function isEndOfInput()
    {
        return self::T_EOI === $this->type;
    }
}
