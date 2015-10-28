<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Mention extends SimpleTerm
{
    /**
     * @param string $word
     */
    public function __construct($word)
    {
        parent::__construct(QueryLexer::T_MENTION, $word);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'Mention',
            'Term' => $this->getToken()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitMention($this);
    }
}
