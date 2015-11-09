<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

abstract class AbstractQueryItem
{
    /**
     * @var bool
     */
    protected $excluded = false;

    /**
     * @var bool
     */
    protected $included = false;

    /**
     * @var float
     */
    protected $boostBy = null;

    /**
     * @param bool $bool
     *
     * @return self
     */
    public function setExcluded($bool)
    {
        $this->excluded = (bool)$bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExcluded()
    {
        return $this->excluded;
    }

    /**
     * @param bool $bool
     *
     * @return self
     */
    public function setIncluded($bool)
    {
        $this->included = (bool)$bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncluded()
    {
        return $this->included;
    }

    /**
     * @param float $boostBy
     *
     * @return self
     */
    public function setBoostBy($boostBy)
    {
        $this->boostBy = $boostBy;

        return $this;
    }

    /**
     * @return float
     */
    public function getBoostBy()
    {
        return $this->boostBy;
    }

    /**
     * @return bool
     */
    public function isBoosted()
    {
        return (bool)$this->boostBy;
    }

    /**
     * @return array
     */
    abstract public function toArray();

    /**
     * @param int $tokenType
     *
     * @return array
     */
    abstract public function getQueryItemsByTokenType($tokenType = null);

    /**
     * @param QueryItemVisitorInterface $visitor
     *
     * @return mixed
     */
    abstract public function accept(QueryItemVisitorInterface $visitor);
}
