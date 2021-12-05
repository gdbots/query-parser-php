<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Subquery extends Node
{
    const NODE_TYPE = 'subquery';
    const COMPOUND_NODE = true;

    /** @var Node[] */
    private array $nodes = [];

    public function __construct(
        array $nodes,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST
    ) {
        parent::__construct(null, $boolOperator, $useBoost, $boost);
        $this->nodes = $nodes;

        foreach ($this->nodes as $node) {
            if ($node->isCompoundNode()) {
                throw new \LogicException('A Subquery cannot contain compound nodes.  (Field, Range, Subquery)');
            }
        }
    }

    public static function fromArray(array $data = []): self
    {
        $useBoost = (bool)($data['use_boost'] ?? false);
        $boost = (float)($data['boost'] ?? self::DEFAULT_BOOST);

        $nodes = [];
        if (isset($data['nodes'])) {
            foreach ($data['nodes'] as $node) {
                $nodes[] = self::factory($node);
            }
        }

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::from($data['bool_operator']) : null;
        } catch (\Throwable $e) {
            $boolOperator = null;
        }

        return new self($nodes, $boolOperator, $useBoost, $boost);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['nodes'] = [];

        foreach ($this->nodes as $node) {
            $array['nodes'][] = $node->toArray();
        }

        return $array;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addSubquery($this);
    }
}
