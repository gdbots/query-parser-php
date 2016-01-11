<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;

abstract class Range extends Node
{
    const SUPPORTS_BOOST = false;
    const COMPOUND_NODE = true;

    /** @var Node */
    private $lowerNode;

    /** @var Node */
    private $upperNode;

    /** @var bool */
    private $exclusive = false;

    /**
     * Range constructor.
     *
     * @param Node|null $lowerNode
     * @param Node|null $upperNode
     * @param bool $exclusive
     *
     * @throws \LogicException
     */
    public function __construct(Node $lowerNode = null, Node $upperNode = null, $exclusive = false)
    {
        parent::__construct(null);
        $this->lowerNode = $lowerNode;
        $this->upperNode = $upperNode;
        $this->exclusive = (bool)$exclusive;

        if (null === $this->lowerNode && null === $this->upperNode) {
            throw new \LogicException('Range requires at least a lower or upper node.');
        }
    }

    /**
     * @param array $data
     * @return self
     */
    final public static function fromArray(array $data = [])
    {
        $lowerNode = isset($data['lower_node']) ? self::factory($data['lower_node']) : null;
        $upperNode = isset($data['upper_node']) ? self::factory($data['upper_node']) : null;
        $exclusive = isset($data['exclusive']) ? (bool)$data['exclusive'] : false;
        return new static($lowerNode, $upperNode, $exclusive);
    }

    /**
     * @return array
     */
    final public function toArray()
    {
        $array = parent::toArray();

        if (null !== $this->lowerNode) {
            $array['lower_node'] = $this->lowerNode;
        }

        if (null !== $this->upperNode) {
            $array['upper_node'] = $this->upperNode;
        }

        if ($this->exclusive) {
            $array['exclusive'] = $this->exclusive;
        }

        return $array;
    }

    /**
     * @return bool
     */
    final public function hasLowerNode()
    {
        return null !== $this->lowerNode;
    }

    /**
     * @return Node
     */
    public function getLowerNode()
    {
        return $this->lowerNode;
    }

    /**
     * @return bool
     */
    final public function hasUpperNode()
    {
        return null !== $this->upperNode;
    }

    /**
     * @return Node
     */
    public function getUpperNode()
    {
        return $this->upperNode;
    }

    /**
     * @return bool
     */
    final public function isInclusive()
    {
        return !$this->exclusive;
    }

    /**
     * @return bool
     */
    final public function isExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @param QueryBuilder $builder
     */
    final public function acceptBuilder(QueryBuilder $builder)
    {
        $builder->addRange($this);
    }
}
