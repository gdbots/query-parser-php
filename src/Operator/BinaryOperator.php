<?php

namespace Gdbots\QueryParser\Operator;

use Closure;
use Gdbots\QueryParser\OperatorInterface;

/**
 * Represents a binary operator
 */
class BinaryOperator implements OperatorInterface
{
    /**
     * The associativity of the operators
     *
     * @var int
     */
    const LEFT = 1;
    const RIGHT = 2;

    /**
     * The token code
     *
     * @var int
     */
    private $code = null;

    /**
     * The precedence of the operator
     *
     * @var int
     */
    private $precedence = null;

    /**
     * The associativity of the operator, (LEFT or RIGHT)
     *
     * @var int
     */
    private $associativity = null;

    /**
     * The closure which instantiates the node object for this operator
     *
     * @var Closure
     */
    private $node = null;

    /**
     * The class constructor.
     *
     * @param int     $code
     * @param int     $precedence
     * @param int     $associativity
     * @param Closure $node
     */
    public function __construct($code, $precedence, $associativity, Closure $node)
    {
        $this->code = $code;
        $this->precedence = $precedence;
        $this->associativity = $associativity;
        $this->node = $node;
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
     * Returns the precedence of the operator
     *
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * Indicates if the operator is left associative
     *
     * @return bool
     */
    public function isLeftAssociative()
    {
        return $this->associativity == self::LEFT;
    }

    /**
     * Indicates if the operator is right associative
     *
     * @return bool
     */
    public function isRightAssociative()
    {
        return $this->associativity == self::RIGHT;
    }

    /**
     * Returns the closure which instantiates the node object for this operator
     *
     * @return Closure
     */
    public function getNode()
    {
        return $this->node;
    }
}
