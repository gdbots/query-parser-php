<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Hashtag extends AbstractSimpleTerm
{
    /**
     * @param string $word
     */
    public function __construct($word)
    {
        parent::__construct(QueryLexer::T_HASHTAG, $word);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Operator' => 'Hashtag',
            'Term' => $this->getToken()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitHashtag($this);
    }
}
