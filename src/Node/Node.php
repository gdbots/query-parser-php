<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

abstract class Node implements \JsonSerializable
{
    const NODE_TYPE = 'node';
    const COMPOUND_NODE = false;

    // "^" BOOST refers to scoring parts of the query.
    const SUPPORTS_BOOST = true;
    const DEFAULT_BOOST = 1.0;
    const MIN_BOOST = 0.0;
    const MAX_BOOST = 10.0;

    // "~" FUZZY refers to fuzzy matching terms and proximity on phrases and distance
    const SUPPORTS_FUZZY = false;
    const DEFAULT_FUZZY = 1;
    const MIN_FUZZY = 1;
    const MAX_FUZZY = 2;

    /** @var mixed */
    private $value = null;

    private BoolOperator $boolOperator;
    private bool $useBoost = false;
    private float $boost = self::DEFAULT_BOOST;
    private bool $useFuzzy = false;
    private int $fuzzy = self::DEFAULT_FUZZY;

    public function __construct(
        $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY
    ) {
        $this->value = $value;
        $this->boolOperator = $boolOperator ?: BoolOperator::OPTIONAL;

        $this->useBoost = $useBoost && static::SUPPORTS_BOOST && $this->boolOperator === BoolOperator::OPTIONAL;
        if ($this->useBoost) {
            $this->boost = $boost;
            if ($this->boost < static::MIN_BOOST) {
                $this->boost = static::MIN_BOOST;
            }

            if ($this->boost > static::MAX_BOOST) {
                $this->boost = static::MAX_BOOST;
            }
        }

        $this->useFuzzy = $useFuzzy && static::SUPPORTS_FUZZY && $this->boolOperator === BoolOperator::OPTIONAL;
        if ($this->useFuzzy) {
            $this->fuzzy = min(max($fuzzy, static::MIN_FUZZY), static::MAX_FUZZY);
        }
    }

    public static function factory(array $data = []): self
    {
        $type = $data['type'];
        // fix for php7 reserved name (scalar type hint)
        if ('number' === $type) {
            $type = 'numbr';
        }

        /** @var Node $class */
        $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
        $class = 'Gdbots\QueryParser\Node\\' . $camel;
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Node type [%s] does not exist.', $type));
        }

        return $class::fromArray($data);
    }

    public function toArray(): array
    {
        $array = ['type' => static::NODE_TYPE];

        if ($this->hasValue()) {
            $array['value'] = $this->value;
        }

        if (!$this->isOptional()) {
            $array['bool_operator'] = $this->boolOperator->value;
        }

        if ($this->useBoost) {
            $array['use_boost'] = $this->useBoost;
            $array['boost'] = $this->boost;
        }

        if ($this->useFuzzy) {
            $array['use_fuzzy'] = $this->useFuzzy;
            $array['fuzzy'] = $this->fuzzy;
        }

        return $array;
    }

    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    final public function hasValue(): bool
    {
        return null !== $this->value && '' !== $this->value;
    }

    final public function getValue()
    {
        return $this->value;
    }

    final public function getBoolOperator(): BoolOperator
    {
        return $this->boolOperator;
    }

    final public function isOptional(): bool
    {
        return $this->boolOperator === BoolOperator::OPTIONAL;
    }

    final public function isRequired(): bool
    {
        return $this->boolOperator === BoolOperator::REQUIRED;
    }

    final public function isProhibited(): bool
    {
        return $this->boolOperator === BoolOperator::PROHIBITED;
    }

    final public function isCompoundNode(): bool
    {
        return static::COMPOUND_NODE;
    }

    public function useComparisonOperator(): bool
    {
        return false;
    }

    final public function useBoost(): bool
    {
        return $this->useBoost;
    }

    final public function getBoost(): float
    {
        return $this->boost;
    }

    final public function useFuzzy(): bool
    {
        return $this->useFuzzy;
    }

    final public function getFuzzy(): int
    {
        return $this->fuzzy;
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        // do nothing
    }
}
