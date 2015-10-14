<?php

namespace Gdbots\QueryParser\Base;

use Gdbots\QueryParser\Grammar as ParserGrammar;
use Gdbots\QueryParser\Operator\UnaryOperator;
use Gdbots\QueryParser\Operator\BinaryOperator;
use Gdbots\QueryParser\Base\Node\HashtagOperator;
use Gdbots\QueryParser\Base\Node\RequireOperator;
use Gdbots\QueryParser\Base\Node\ExcludeOperator;
use Gdbots\QueryParser\Base\Node\BinaryField;

/**
 * Parser grammar for the query
 */
class Grammar extends ParserGrammar
{
    /**
     * Creates the grammar
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOperator(new UnaryOperator(Lexer::T_HASHTAG, 3, function ($node) {
            return new HashtagOperator($node);
        }));
        $this->addOperator(new UnaryOperator(Lexer::T_REQUIRE, 2, function ($node) {
            return new RequireOperator($node);
        }));
        $this->addOperator(new UnaryOperator(Lexer::T_EXCLUDE, 2, function ($node) {
            return new ExcludeOperator($node);
        }));

        $this->addOperator(new BinaryOperator(Lexer::T_COLON, 4, BinaryOperator::LEFT, function ($left, $right) {
            return new BinaryField($left, $right);
        }));
    }
}
