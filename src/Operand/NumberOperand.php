<?php

namespace Gdbots\QueryParser\Operand;

use Gdbots\QueryParser\OperandInterface;
use Gdbots\QueryParser\Node\Operand;
use Gdbots\QueryParser\Lexer;
use Gdbots\QueryParser\TokenStream;
use Gdbots\QueryParser\Grammar;

/**
 * Operand which parses integer and floating-point values
 */
class NumberOperand implements OperandInterface
{
    /**
     * Returns the identifiers for this operand
     *
     * @return array
     */
    public function getIdentifiers()
    {
        return [
            Lexer::T_NUMBER,
        ];
    }

    /**
     * Parse the operand
     *
     * @param Grammar     $grammar The grammar of the parser
     * @param TokenStream $stream  The token stream to parse
     *
     * @return Operand The operand node
     */
    public function parse(Grammar $grammar, TokenStream $stream)
    {
        $token = $stream->current();
        $node = new Operand($token->getValue());

        return $node;
    }
}
