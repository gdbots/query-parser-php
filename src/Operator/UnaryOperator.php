<?php

namespace Gdbots\QueryParser\Operator;

use Closure;
use Gdbots\QueryParser\OperatorInterface;

/**
 * Represents a prefix unary operator
 */
class UnaryOperator implements OperatorInterface
{
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
     * The closure which instantiates the node object for this operator
     *
     * @var Closure
     */
    private $node = null;

    /**
     * The class constructor
     *
     * @param int     $code
     * @param int     $precedence
     * @param Closure $node
     */
    public function __construct($code, $precedence, Closure $node)
    {
        $this->code = $code;
        $this->precedence = $precedence;
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
     * Returns the closure which instantiates the node object for this operator
     *
     * @return Closure
     */
    public function getNode()
    {
        return $this->node;
    }
}
