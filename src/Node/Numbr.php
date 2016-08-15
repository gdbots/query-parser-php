<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\ComparisonOperator;

/**
 * Class is intentionally misspelled so it doesn't conflict with PHP7 scalar type hints.
 */
final class Numbr extends Node
{
    const NODE_TYPE = 'number';

    /** @var ComparisonOperator */
    private $comparisonOperator;

    /**
     * Numbr constructor.
     *
     * @param float $value
     * @param ComparisonOperator $comparisonOperator
     */
    public function __construct($value, ComparisonOperator $comparisonOperator = null)
    {
        parent::__construct($value, null);
        $this->comparisonOperator = $comparisonOperator ?: ComparisonOperator::EQ();
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $value = isset($data['value']) ? (float)$data['value'] : null;

        try {
            $comparisonOperator = isset($data['comparison_operator']) ? ComparisonOperator::create($data['comparison_operator']) : null;
        } catch (\Exception $e) {
            $comparisonOperator = null;
        }

        return new self($value, $comparisonOperator);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        if ($this->comparisonOperator->equals(ComparisonOperator::EQ())) {
            return $array;
        }

        $array['comparison_operator'] = $this->comparisonOperator->getValue();
        return $array;
    }

    /**
     * @return bool
     */
    public function useComparisonOperator()
    {
        return !$this->comparisonOperator->equals(ComparisonOperator::EQ());
    }

    /**
     * @return ComparisonOperator
     */
    public function getComparisonOperator()
    {
        return $this->comparisonOperator;
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder)
    {
        $builder->addNumber($this);
    }
}
