<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Operator\UnaryOperator;
use Gdbots\QueryParser\Operator\BinaryOperator;
use Gdbots\QueryParser\Node\LogicalNeg;
use Gdbots\QueryParser\Node\LogicalAnd;
use Gdbots\QueryParser\Node\LogicalOr;

/**
 * Parser grammar for the query
 */
class Grammar extends AbstractGrammar
{
    /**
     * Creates the grammar
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOperator(new UnaryOperator(Lexer::T_NOT, 5, function ($node) {
            return new LogicalNeg($node);
        }));
        $this->addOperator(new BinaryOperator(Lexer::T_AND, 1, BinaryOperator::LEFT, function ($left, $right) {
            return new LogicalAnd($left, $right);
        }));
        $this->addOperator(new BinaryOperator(Lexer::T_OR, 1, BinaryOperator::LEFT, function ($left, $right) {
            return new LogicalOr($left, $right);
        }));
    }
}
