<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

abstract class QueryItem
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * @param int $tokenType
     *
     * @return array
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        return [];
    }

    /**
     * @param QueryItemVisitorinterface $visitor
     */
    abstract public function accept(QueryItemVisitorinterface $visitor);
}
