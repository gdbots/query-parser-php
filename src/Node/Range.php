<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;

abstract class Range extends Node
{
    const SUPPORTS_BOOST = false;
    const COMPOUND_NODE = true;

    private ?Node $lowerNode = null;
    private ?Node $upperNode = null;
    private bool $exclusive = false;

    public function __construct(?Node $lowerNode = null, ?Node $upperNode = null, bool $exclusive = false)
    {
        parent::__construct(null);
        $this->lowerNode = $lowerNode;
        $this->upperNode = $upperNode;
        $this->exclusive = $exclusive;

        if (null === $this->lowerNode && null === $this->upperNode) {
            throw new \LogicException('Range requires at least a lower or upper node.');
        }
    }

    final public static function fromArray(array $data = []): self
    {
        $lowerNode = isset($data['lower_node']) ? self::factory($data['lower_node']) : null;
        $upperNode = isset($data['upper_node']) ? self::factory($data['upper_node']) : null;
        $exclusive = isset($data['exclusive']) ? (bool)$data['exclusive'] : false;
        return new static($lowerNode, $upperNode, $exclusive);
    }

    final public function toArray(): array
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

    final public function hasLowerNode(): bool
    {
        return null !== $this->lowerNode;
    }

    public function getLowerNode(): ?Node
    {
        return $this->lowerNode;
    }

    final public function hasUpperNode(): bool
    {
        return null !== $this->upperNode;
    }

    public function getUpperNode(): ?Node
    {
        return $this->upperNode;
    }

    final public function isInclusive(): bool
    {
        return !$this->exclusive;
    }

    final public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    final public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addRange($this);
    }
}
