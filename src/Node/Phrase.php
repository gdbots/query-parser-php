<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Phrase extends Node
{
    const NODE_TYPE = 'phrase';
    const SUPPORTS_FUZZY = true;

    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
    }

    public static function fromArray(array $data = []): self
    {
        $value = $data['value'] ?? '';
        $useBoost = (bool)($data['use_boost'] ?? false);
        $boost = (float)($data['boost'] ?? self::DEFAULT_BOOST);
        $useFuzzy = (bool)($data['use_fuzzy'] ?? false);
        $fuzzy = (int)($data['fuzzy'] ?? self::DEFAULT_FUZZY);

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Throwable $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addPhrase($this);
    }
}
