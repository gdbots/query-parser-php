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
        if ((string)floatval($word) == $word) {
            $tokenType = QueryLexer::T_NUMBER;
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
