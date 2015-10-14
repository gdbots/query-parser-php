<?php

namespace Gdbots\QueryParser;

/**
 * Represents an operand
 */
interface OperandInterface
{
    /**
     * Returns the identifiers for an operand
     *
     * @return array
     */
    public function getIdentifiers();

    /**
     * Parse the operand
     *
     * @param Grammar     $grammar The grammar of the parser
     * @param TokenStream $stream  The token stream to parse
     *
     * @return Node The parsed operand node
     */
    public function parse(Grammar $grammar, TokenStream $stream);
}
