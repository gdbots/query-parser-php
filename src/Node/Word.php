<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Word extends AbstractSimpleTerm
{
    /**
     * @param string $word
     * @param int    $tokenType
     */
    public function __construct($word, $tokenType = QueryLexer::T_WORD)
    {
        if (preg_match(QueryLexer::REGEX_NUMBER, $word)) {
            $tokenType = QueryLexer::T_NUMBER;
        }

        if (preg_match(QueryLexer::REGEX_DATE, $word)) {
            $tokenType = QueryLexer::T_DATE;
        }

        parent::__construct($tokenType, $word);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Expression' => 'Word',
            'Term' => $this->getToken()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitWord($this);
    }
}
