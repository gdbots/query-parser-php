<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\ComparisonOperator;

/**
 * Class is intentionally misspelled so it doesn't conflict with PHP7 scalar type hints.
 */
final class Numbr extends Node
{
    const NODE_TYPE = 'number';
    private ComparisonOperator $comparisonOperator;

    public function __construct(float $value, ?ComparisonOperator $comparisonOperator = null)
    {
        parent::__construct($value, null);
        $this->comparisonOperator = $comparisonOperator ?: ComparisonOperator::EQ;
    }

    public static function fromArray(array $data = []): self
    {
        $value = (float)($data['value'] ?? 0.0);

        try {
            $comparisonOperator = isset($data['comparison_operator']) ? ComparisonOperator::from($data['comparison_operator']) : null;
        } catch (\Throwable $e) {
            $comparisonOperator = null;
        }

        return new self($value, $comparisonOperator);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if ($this->comparisonOperator === ComparisonOperator::EQ) {
            return $array;
        }

        $array['comparison_operator'] = $this->comparisonOperator->value;
        return $array;
    }

    public function useComparisonOperator(): bool
    {
        return $this->comparisonOperator !== ComparisonOperator::EQ;
    }

    public function getComparisonOperator(): ComparisonOperator
    {
        return $this->comparisonOperator;
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addNumber($this);
    }
}
