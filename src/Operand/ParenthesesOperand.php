<?php

namespace Gdbots\QueryParser\Operand;

use Gdbots\QueryParser\Exception\SyntaxErrorException;
use Gdbots\QueryParser\Lexer;
use Gdbots\QueryParser\TokenStream;
use Gdbots\QueryParser\Grammar;
use Gdbots\QueryParser\Parser;
use Gdbots\QueryParser\OperandInterface;

/**
 * Operand which parses expressions between parentheses
 */
class ParenthesesOperand implements OperandInterface
{
    /**
     * Returns the identifiers for this operand.
     *
     * @return array The identifiers for this operand.
     */
    public function getIdentifiers()
    {
        return [
            Lexer::T_OPEN_PARENTHESIS,
        ];
    }

    /**
     * Parse the operand
     *
     * @param Grammar     $grammar The grammar of the parser
     * @param TokenStream $stream  The token stream to parse
     *
     * @return Node The node between the parentheses
     */
    public function parse(Grammar $grammar, TokenStream $stream)
    {
        $stream->next();

        $parser = new Parser($grammar);
        $node = $parser->parse($stream);

        $stream->expect([ Lexer::T_CLOSE_PARENTHESIS ], function (Token $current = null) {
            // thrown if an unexpected token was found
            throw new SyntaxErrorException($current
                ? 'Expected `)`; got `' . $current->getValue() . '}`'
                : 'Expected `)` but end of stream reached'
            );
        });

        return $node;
    }
}
