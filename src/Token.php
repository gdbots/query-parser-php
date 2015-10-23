<?php

namespace Gdbots\QueryParser;


class Token
{

    const T_NONE        = 0;
    const T_KEYWORD     = 1;
    const T_MENTION     = 2;
    const T_HASHTAG     = 3;
    const T_PHRASE      = 4;
    const T_INCLUDE     = 5;
    const T_EXCLUDE     = 6;
    const T_FILTER_GT   = 7;
    const T_FILTER_GTE  = 8;
    const T_FILTER_LT   = 9;
    const T_FILTER_LTE  = 10;
    const T_FILTER_EQ   = 11;


    /**
     * The token type
     * @var int
     */
    protected $type;
    /**
     * The token data
     * @var string
     */
    protected $data = null;
    /**
     * The token start position
     * @var int
     */
    protected $startPos = null;

    /**
     * The value filter
     * @var string
     */
    protected $value = null;

    /**
     * The boost value
     * @var int
     */
    protected $boost;

    /**
     * Create a new token
     * @param int $type The token type
     * @param int $startPos The token start position
     */
    public function __construct($type, $startPos=null)
    {
        $this->type = $type;
        $this->startPos = null;
    }

    /**
     * Append data to the token
     * @param string $data
     */
    public function addData($data)
    {
        $this->data.=$data;
    }

    /**
     * Updates the token type
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Updates the token type if it is {@link self::T_NONE}
     * @param int $type
     */
    public function setTypeIfNone($type)
    {
        if ($this->type == self::T_NONE)
            $this->type = $type;
    }

    /**
     * Check if the token type is {@link self::T_NONE} or the given token
     * @param int $type
     * @return bool
     */
    public function isTypeNoneOr($type)
    {
        return ($this->type == self::T_NONE || $this->type == $type);
    }

    /**
     * Gets the token type
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the boost value
     * @return int
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Sets the boost
     * @param int $boost
     */
    public function setBoost($boost)
    {
        $this->boost = $boost;
    }

    /**
     * Checks to see if boost value is set
     * @retrun boolean
     */
    public function hasBoost()
    {
        if (!empty($this->boost)) {
            return true;
        }
        return false;
    }

    /**
     * Gets the token data
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the token data
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the token start position in the string

     * @return int
     */
    public function getStartPosition()
    {
        return $this->startPos;
    }

    /**
     * Sets the token start position
     * @param int $startPos
     */
    public function setStartPosition($startPos)
    {
        $this->startPos = $startPos;
        $this->startPos = null;
    }

    /**
     * Gets the token's name.
     * @param int $token A token.
     * @return string
     */
    public static function getName($token)
    {
        $refl = new \ReflectionClass(__CLASS__);
        $constants = $refl->getConstants();
        $name = array_search($token, $constants);
        if ($name)
            return $name;
        return 'UNKNOWN_TOKEN';
    }

    /**
     * Gets the token value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the token value
     * @param string $value
     */
    public function setvalue($value)
    {
        $this->value = $value;
    }
}
