<?php

namespace Gdbots\QueryParser\Node;

class Subquery extends Node
{
    const NODE_TYPE = 'subquery';

    /** @var Node[] */
    private $nodes = [];

    /**
     * Subquery constructor.
     *
     * @param Node[] $nodes
     * @param bool $useBoost
     * @param float|mixed $boost
     */
    public function __construct(array $nodes, $useBoost = false, $boost = self::DEFAULT_BOOST)
    {
        parent::__construct(null, null, $useBoost, $boost);
        $this->nodes = $nodes;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost    = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;

        $nodes = [];
        if (isset($data['nodes'])) {
            foreach ($data['nodes'] as $node) {
                $nodes[] = self::factory($node);
            }
        }

        return new self($nodes, $useBoost, $boost);
    }

    /**
     * @return array
     */
    public function toArray()
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
    public function getNodes()
    {
        return $this->nodes;
    }
}
