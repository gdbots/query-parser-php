<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Exception\SyntaxErrorException;
use Gdbots\QueryParser\Exception\InvalidIdentifierException;
use Gdbots\QueryParser\Node\Operand;
use Exception;

/**
 * Implementation of an operator precedence parser based on the "Precedence climbing" algorithm
 */
class Parser
{
    /**
     * The grammar used for this parser
     *
     * @var Grammar
     */
    private $grammar = null;

    /**
     * The stream to parse
     *
     * @var TokenStream
     */
    private $stream = null;

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
     * The class constructor
     *
     * @param Grammar $grammar
     */
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
        $this->operators = $this->grammar->getOperators();
        $this->operands = $this->grammar->getOperands();
    }

    /**
     * Parse the token stream and return the resulting parse tree
     *
     * @param TokenStream $stream The stream to parse
     *
     * @return Node The root of the node tree
     */
    public function parse(TokenStream $stream)
    {
        $this->stream = $stream;

        $node = $this->parseExpression(0);

        return $node;
    }

    /**
     * Parse the expression
     *
     * @param int $precedence The precedence level
     *
     * @return Node The expression node
     */
    private function parseExpression($precedence)
    {
        $node = $this->parsePrimary();

        while (($token = $this->stream->current()) && $this->operators->isBinary($token)) {
            $operator = $this->operators->getBinaryOperator($token);

            if ($operator->getPrecedence() < $precedence) {
                break;
            }

            $this->stream->next();
            $operatorNode = $operator->getNode();
            $node = $operatorNode($node, $this->parseExpression($operator->getPrecedence() + !$operator->isRightAssociative()));
        }

        while ($token = $this->stream->current()) {
            try {
                $node = new Operand($node->evaluate() . ' ' . $this->parseExpression(0)->evaluate());
            } catch (Exception $e) {
                break;
            }
        }

        return $node;
    }

    /**
     * Parse primary expression
     *
     * @return Node The primary node
     *
     * @throws SyntaxErrorException if no operand or unary operator can be found
     */
    private function parsePrimary()
    {
        $token = $this->stream->current();
        if ($token && $this->operators->isUnary($token)) {
            $this->stream->next();
            $operator = $this->operators->getUnaryOperator($token);
            $operatorNode = $operator->getNode();
            $node = $operatorNode($this->parseExpression($operator->getPrecedence()));
        } elseif ($token) {
            $node = $this->parseOperand();
            $this->stream->next();
        } else {
            throw new SyntaxErrorException('Operand or unary operator expected; but end of stream reached');
        }

        return $node;
    }

    /**
     * Parse the operand
     *
     * @return Node The node object for the operand
     *
     * @throws SyntaxErrorException
     */
    private function parseOperand()
    {
        $token = $this->stream->current();

        try {
            $operator = $this->operands->getOperand($token);
        } catch (InvalidIdentifierException $e) {
            // thrown if no operand parser can be found for the current token
            throw new SyntaxErrorException('Cannot find operand parser for token `' . $token->getValue(). '`', 0, $e);
        }

        return $operator->parse($this->grammar, $this->stream);
    }
}
