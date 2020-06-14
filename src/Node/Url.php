<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Url extends Node
{
    const NODE_TYPE = 'url';

    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost);
    }

    public static function fromArray(array $data = []): self
    {
        $value = $data['value'] ?? '';
        $useBoost = (bool)($data['use_boost'] ?? false);
        $boost = (float)($data['boost'] ?? self::DEFAULT_BOOST);

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Throwable $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost);
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addUrl($this);
    }
}
