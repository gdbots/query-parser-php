<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\FilterType;

final class Filter extends Node
{
    const NODE_TYPE = 'filter';

    /** @var Node */
    protected $node;

    /** @var Range */
    protected $range;

    /** @var Subquery */
    protected $subquery;

    /** @var FilterType */
    protected $filterType;

    /**
     * Filter constructor.
     *
     * @param string $field
     * @param BoolOperator $boolOperator
     * @param bool $useBoost
     * @param float $boost
     * @param Node $node
     * @param Range $range
     * @param Subquery $subquery
     */
    public function __construct(
        $field,
        BoolOperator $boolOperator = null,
        $useBoost = false,
        $boost = self::DEFAULT_BOOST,
        Node $node = null,
        Range $range = null,
        Subquery $subquery = null
    ) {
        parent::__construct($field, $boolOperator, $useBoost, $boost);

        if (null !== $node) {
            $this->node = $node;
            $this->filterType = FilterType::SIMPLE();
        } elseif (null !== $range) {
            $this->range = $range;
            $this->filterType = FilterType::RANGE();
        } elseif (null !== $subquery) {
            $this->subquery = $subquery;
            $this->filterType = FilterType::SUBQUERY();
        }
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data = [])
    {
        $field    = isset($data['value']) ? $data['value'] : null;
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost    = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        $node = null;
        $range = null;
        $subquery = null;

        if (isset($data['node'])) {
            $node = static::factory($data['node']);
        } elseif (isset($data['range'])) {
            $range = static::factory($data['range']);
        } elseif (isset($data['subquery'])) {
            $subquery = static::factory($data['subquery']);
        }

        return new self($field, $boolOperator, $useBoost, $boost, $node, $range, $subquery);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (null !== $this->node) {
            $array['node'] = $this->node->toArray();
        } elseif (null !== $this->range) {
            $array['range'] = $this->range->toArray();
        } elseif (null !== $this->subquery) {
            $array['subquery'] = $this->subquery->toArray();
        }

        return $array;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->getValue();
    }

    /**
     * @return FilterType
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return Range
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return Subquery
     */
    public function getSubquery()
    {
        return $this->subquery;
    }
}
