<?php
declare(strict_types=1);

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;

final class ParsedQuery implements \JsonSerializable
{
    private array $nodes = [];
    private array $nodesByType = [];

    public static function fromArray(array $data = []): self
    {
        $obj = new static();

        foreach ($data as $v) {
            $obj->addNode(Node::factory($v));
        }

        return $obj;
    }

    public function toArray(): array
    {
        return $this->nodes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param Node[] $nodes
     *
     * @return self
     */
    public function addNodes(array $nodes): self
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        return $this;
    }

    /**
     * @param Node $node
     *
     * @return self
     */
    public function addNode(Node $node): self
    {
        $this->nodes[] = $node;
        $this->nodesByType[$node::NODE_TYPE][] = $node;
        return $this;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @param string $type
     *
     * @return Node[]
     */
    public function getNodesOfType(string $type): array
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
    public function hasAMatchableNode(): bool
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
     * @return string[]
     */
    public function getFieldsUsed(): array
    {
        $fields = [];

        /** @var Field $node */
        foreach ($this->getNodesOfType(Field::NODE_TYPE) as $node) {
            $fields[$node->getName()] = true;
        }

        return array_keys($fields);
    }
}
