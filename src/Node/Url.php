<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Url extends SimpleTerm
{
    /**
     * @param string $url
     */
    public function __construct($url)
    {
        parent::__construct(QueryScanner::T_URL, $url);
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
        $visitor->visitUrl($this);
    }
}
