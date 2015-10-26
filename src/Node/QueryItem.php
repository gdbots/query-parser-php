<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

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
        return array_key_exists($parentTokenType, $this->parentTokenTypes);
    }

    /**
     * @param array $parentTokenTypes
     *
     * @return QueryItem
     */
    public function setParentTokenTypes(array $parentTokenTypes)
    {
        $this->parentTokenTypes = [];

        foreach ($parentTokenTypes as $parentTokenType => $value) {
            $this->addParentTokenType($parentTokenType, $value);
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
     * @param int   $parentTokenType
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParentTokenType($parentTokenType, $default = null)
    {
        if ($this->hasParentTokenType($parentTokenType)) {
            return $this->parentTokenTypes[$parentTokenType];
        }

        return $default;
    }

    /**
     * @param int   $parentTokenType
     * @param mixed $value
     *
     * @return QueryItem
     */
    public function addParentTokenType($parentTokenType, $value = null)
    {
        if (!$this->hasParentTokenType($parentTokenType)) {
            $this->parentTokenTypes[$parentTokenType] = $value;
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
            unset($this->parentTokenTypes[$parentTokenType]);
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
     * @param QueryItemVisitorInterface $visitor
     *
     * @return mixed
     */
    abstract public function accept(QueryItemVisitorInterface $visitor);
}
