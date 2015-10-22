<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

abstract class QueryItem
{
    /**
     * @var array
     */
    protected $parentTokenTypes = [];

    /**
     * @param int $parentTokenType
     *
     * @return bool
     */
    public function hasParentTokenType($parentTokenType)
    {
        return in_array($parentTokenType, $this->parentTokenTypes);
    }

    /**
     * @param array $parentTokenTypes
     *
     * @return QueryItem
     */
    public function setParentTokenTypes(array $parentTokenTypes)
    {
        $this->parentTokenTypes = [];

        foreach ($parentTokenTypes as $parentTokenType) {
            $this->addParentTokenType($parentTokenType);
        }

        return $this;
    }

    /**
     * @return \Traversable
     */
    public function getParentTokenTypes()
    {
        return $this->parentTokenTypes ?: $this->parentTokenTypes = [];
    }

    /**
     * @param int $parentTokenType
     *
     * @return QueryItem
     */
    public function addParentTokenType($parentTokenType)
    {
        if (!$this->hasParentTokenType($parentTokenType)) {
            $this->getParentTokenTypes[] = $parentTokenType;
        }

        return $this;
    }

    /**
     * @param int $parentTokenType
     *
     * @return QueryItem
     */
    public function removeParentTokenType($parentTokenType)
    {
        if ($this->hasParentTokenType($parentTokenType)) {
            unset($this->getParentTokenTypes[array_search($parentTokenType, $this->getParentTokenTypes)]);
        }

        return $this;
    }

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
