<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Field extends Node
{
    const NODE_TYPE = 'field';
    const COMPOUND_NODE = true;

    /**
     * Associative array of ['aliased_field_name' => 'real_field_name'].
     * For example: plays:>100 should actually be: plays_count:>100.
     *
     * @var array
     */
    public static array $aliases = [];
    private Node $node;

    public function __construct(
        string $fieldName,
        Node $node,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST
    ) {
        if (isset(self::$aliases[$fieldName])) {
            $fieldName = self::$aliases[$fieldName];
        }

        parent::__construct($fieldName, $boolOperator, $useBoost, $boost);
        $this->node = $node;

        if ($this->node instanceof Field) {
            throw new \LogicException('A Field cannot contain another field.');
        }
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

        /** @var Node $node */
        $node = isset($data['node']) ? self::factory($data['node']) : null;

        return new self($value, $node, $boolOperator, $useBoost, $boost);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['node'] = $this->node->toArray();
        return $array;
    }

    public function getName(): string
    {
        return $this->getValue();
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function hasCompoundNode(): bool
    {
        return $this->node->isCompoundNode();
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addField($this);
    }
}
