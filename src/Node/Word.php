<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Word extends SimpleTerm
{
    /**
     * @param string $word
     */
    public function __construct($word)
    {
        parent::__construct(QueryLexer::T_WORD, $word);
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
