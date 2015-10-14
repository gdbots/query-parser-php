<?php

namespace Gdbots\QueryParser;

/**
 * Class which represents an expression token
 */
class Token
{
    /**
     * The token code
     *
     * @var int
     */
    private $code = null;

    /**
     * The token value
     *
     * @var string
     */
    private $value = null;

    /**
     * The token offset
     *
     * @var int
     */
    private $offset = null;

    /**
     * The class constructor
     *
     * @param int    $code
     * @param string $value
     * @param int    $offset
     */
    public function __construct($code, $value, $offset)
    {
        $this->code = $code;
        $this->value = $value;
        $this->offset = $offset;
    }

    /**
     * Returns the token code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the token value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns thr token offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
