<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Operand\NumberOperand;
use Gdbots\QueryParser\Operand\StringOperand;
use Gdbots\QueryParser\Operand\ParenthesesOperand;

/**
 * Clean parser grammar for the query
 */
class AbstractGrammar
{
    /**
     * The operator table
     *
     * @var Operators
     */
    private $operators = null;

    /**
     * The operand table
     *
     * @var Operands
     */
    private $operands = null;

    /**
     * Creates the grammar
     */
    public function __construct()
    {
        $this->operators = new Operators();
        $this->operands = new Operands();

        $this->addOperand(new NumberOperand());
        $this->addOperand(new StringOperand());
        $this->addOperand(new ParenthesesOperand());
    }

    /**
     * Adds a new operator to the operator table
     *
     * @param Operator $operator
     */
    public function addOperator(OperatorInterface $operator)
    {
        $this->operators->addOperator($operator);
    }

    /**
     * Adds a new operand to the operand table
     *
     * @param Operand $operand
     */
    public function addOperand(OperandInterface $operand)
    {
        $this->operands->addOperand($operand);
    }

    /**
     * Returns the instance of the operator table
     *
     * @return Operators
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Returns the instance of the operand table
     *
     * @return Operands
     */
    public function getOperands()
    {
        return $this->operands;
    }
}
