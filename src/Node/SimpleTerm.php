<?php

namespace Gdbots\QueryParser\Node;

abstract class SimpleTerm extends QueryItem
{
    /**
     * @var int
     */
    protected $tokenType;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param int    $tokenType
     * @param string $token
     */
    public function __construct($tokenType, $token)
    {
        $this->tokenType = $tokenType;
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
