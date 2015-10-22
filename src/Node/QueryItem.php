<?php

namespace Gdbots\QueryParser\Node;

use Doctrine\Common\Collections\ArrayCollection;
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
        return $this->getParentTokenTypes()->contains($parentTokenType);
    }

    /**
     * @param array $parentTokenTypes
     *
     * @return QueryItem
     */
    public function setParentTokenTypes(array $parentTokenTypes)
    {
        $this->parentTokenTypes = new ArrayCollection();

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
        return $this->parentTokenTypes ?: $this->parentTokenTypes = new ArrayCollection();
    }

    /**
     * @param int $parentTokenType
     *
     * @return QueryItem
     */
    public function addParentTokenType($parentTokenType)
    {
        if (!$this->getParentTokenTypes()->contains($parentTokenType)) {
            $this->getParentTokenTypes()->add($parentTokenType);
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
        if ($this->getParentTokenTypes()->contains($parentTokenType)) {
            $this->getParentTokenTypes()->removeElement($parentTokenType);
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
