<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Url extends AbstractSimpleTerm
{
    /**
     * @param string $url
     */
    public function __construct($url)
    {
        parent::__construct(QueryLexer::T_URL, $url);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Expression' => 'Url',
            'Term' => $this->getToken()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitUrl($this);
    }
}
