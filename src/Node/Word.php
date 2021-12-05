<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Word extends Node
{
    const NODE_TYPE = 'word';
    const SUPPORTS_FUZZY = true;

    public static array $stopWords = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in', 'into', 'is',
        'it', 'no', 'not', 'of', 'on', 'or', 'such', 'that', 'the', 'their', 'then', 'there',
        'these', 'they', 'this', 'to', 'was', 'will', 'with',
    ];

    private bool $trailingWildcard = false;

    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY,
        bool $trailingWildcard = false
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
        $this->trailingWildcard = $trailingWildcard;
    }

    public static function fromArray(array $data = []): self
    {
        $value = $data['value'] ?? '';
        $useBoost = (bool)($data['use_boost'] ?? false);
        $boost = (float)($data['boost'] ?? self::DEFAULT_BOOST);
        $useFuzzy = (bool)($data['use_fuzzy'] ?? false);
        $fuzzy = (int)($data['fuzzy'] ?? self::DEFAULT_FUZZY);
        $trailingWildcard = (bool)($data['trailing_wildcard'] ?? false);

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::from($data['bool_operator']) : null;
        } catch (\Throwable $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy, $trailingWildcard);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if (!$this->trailingWildcard) {
            return $array;
        }

        $array['trailing_wildcard'] = $this->trailingWildcard;
        return $array;
    }

    public function hasTrailingWildcard(): bool
    {
        return $this->trailingWildcard;
    }

    public function isStopWord(): bool
    {
        return in_array(strtolower($this->getValue()), self::$stopWords);
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addWord($this);
    }
}
