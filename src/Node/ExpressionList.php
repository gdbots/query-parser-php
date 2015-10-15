<?php

namespace Gdbots\QueryParser\Node;

abstract class ExpressionList extends QueryItem implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $expressions;

    /**
     * @param array $expressions
     */
    public function __construct($expressions = array())
    {
        $this->expressions = $expressions;
    }

    /**
     * @return array
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->expressions);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->expressions);
    }
}
