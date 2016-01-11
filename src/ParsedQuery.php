<?php

namespace Gdbots\QueryParser;

use Gdbots\Common\FromArray;
use Gdbots\Common\ToArray;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;

class ParsedQuery implements FromArray, ToArray, \JsonSerializable
{
    /** @var Node[] */
    private $nodes = [];

    /** @var array */
    private $nodesByType = [];

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data = [])
    {
        $obj = new static();

        foreach ($data as $v) {
            $obj->addNode(Node::factory($v));
        }

        return $obj;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->nodes;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param Node[] $nodes
     * @return static
     */
    public function addNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        return $this;
    }

    /**
     * @param Node $node
     * @return static
     */
    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
        $this->nodesByType[$node::NODE_TYPE][] = $node;
        return $this;
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param string $type
     * @return Node[]
     */
    public function getNodesOfType($type)
    {
        return isset($this->nodesByType[$type]) ? $this->nodesByType[$type] : [];
    }

    /**
     * Returns true if the parsed query contains at least one request for an item
     * matching the query.  If all of the nodes are "prohibited" values it
     * can easily review your entire index.
     *
     * @return bool
     */
    public function hasAMatchableNode()
    {
        foreach ($this->nodes as $node) {
            if (!$node->isProhibited()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an array of fields (specifically the field names) that are
     * used in this query.  e.g. "status:active", "status" is the field name.
     *
     * @return array
     */
    public function getFieldsUsed()
    {
        $fields = [];

        /** @var Field $node */
        foreach ($this->getNodesOfType(Field::NODE_TYPE) as $node) {
            $fields[$node->getName()] = true;
        }

        return array_keys($fields);
    }
}
