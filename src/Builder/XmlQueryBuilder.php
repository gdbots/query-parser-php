<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Numbr;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Word;

/**
 * Creates an xml string (or SimpleXmlElement) using
 * the parsed nodes from a ParsedQuery object or via the "add*"
 * methods on this builder.
 *
 * This is primarily for debugging as it generates an easy to
 * read xml tree which represents how the nodes should be
 * used when querying a search service.
 */
class XmlQueryBuilder extends AbstractQueryBuilder
{
    protected string $result = '';
    protected int $indent = 2;

    public function clear(): self
    {
        $this->result = '';
        $this->indent = 2;
        return $this;
    }

    public function toXmlString(): string
    {
        return '<?xml version="1.0"?>' . PHP_EOL . '<query>' . PHP_EOL . rtrim((string)$this->result) . PHP_EOL . '</query>';
    }

    public function toSimpleXmlElement(): \SimpleXMLElement
    {
        try {
            $xml = new \SimpleXMLElement($this->toXmlString());
        } catch (\Throwable $e) {
            $xml = null;
        }

        if ($xml instanceof \SimpleXMLElement) {
            return $xml;
        }

        return new \SimpleXMLElement('<?xml version="1.0"?><query></query>');
    }

    protected function startField(Field $field, bool $cacheable = false): void
    {
        $tag = sprintf('field name="%s"', $field->getName());

        if (!$field->isOptional()) {
            $tag .= sprintf(' bool_operator="%s"', strtolower($field->getBoolOperator()->name));
        }

        if ($cacheable) {
            $tag .= ' cacheable="true"';
        }

        if ($field->useBoost()) {
            $tag .= sprintf(' boost="%s"', $field->getBoost());
        }

        $this->printLine(sprintf('<%s>', $tag));
        $this->indent();
    }

    protected function endField(Field $field, bool $cacheable = false): void
    {
        $this->outdent();
        $this->printLine('</field>');
    }

    protected function handleRange(Range $range, Field $field, bool $cacheable = false): void
    {
        $this->printLine(
            $range->isExclusive() ? '<' . $range::NODE_TYPE . ' exclusive="true">' : '<' . $range::NODE_TYPE . '>'
        );
        $this->indent();
        $this->printLine('<lower_node>');
        $this->indent();

        if ($range->hasLowerNode()) {
            $range->getLowerNode()->acceptBuilder($this);
        } else {
            $this->printLine('<wildcard/>');
        }

        $this->outdent();
        $this->printLine('</lower_node>');
        $this->printLine('<upper_node>');
        $this->indent();

        if ($range->hasUpperNode()) {
            $range->getUpperNode()->acceptBuilder($this);
        } else {
            $this->printLine('<wildcard/>');
        }

        $this->outdent();
        $this->printLine('</upper_node>');
        $this->outdent();

        $this->printLine('</' . $range::NODE_TYPE . '>');
    }

    protected function startSubquery(Subquery $subquery, ?Field $field = null): void
    {
        $tag = $subquery::NODE_TYPE;
        $inField = $field instanceof Field;

        if (!$inField && $subquery->useBoost()) {
            $tag .= sprintf(' boost="%s"', $subquery->getBoost());
        }

        $this->printLine(sprintf('<%s>', $tag));
        $this->indent();
    }

    protected function endSubquery(Subquery $subquery, ?Field $field = null): void
    {
        $this->outdent();
        $this->printLine('</subquery>');
    }

    protected function mustMatch(Node $node, ?Field $field = null): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function shouldMatch(Node $node, ?Field $field = null): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function mustNotMatch(Node $node, ?Field $field = null): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function mustMatchTerm(Node $node, ?Field $field = null, bool $cacheable = false): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function shouldMatchTerm(Node $node, ?Field $field = null): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function mustNotMatchTerm(Node $node, ?Field $field = null, bool $cacheable = false): void
    {
        $this->printSimpleNode(__FUNCTION__, $node, $field);
    }

    protected function printSimpleNode(string $rule, Node $node, ?Field $field = null): void
    {
        if ($this->inRange()) {
            $this->printLine(sprintf('<%s>%s</%s>', $node::NODE_TYPE, $node->getValue(), $node::NODE_TYPE));
            return;
        }

        if ($field instanceof Field) {
            $tag = $node::NODE_TYPE;
            if ($node instanceof Word && $node->hasTrailingWildcard()) {
                $tag .= ' trailing_wildcard="true"';
            }
        } else {
            $tag = $node::NODE_TYPE;
            if ($node->useBoost()) {
                $tag .= sprintf(' boost="%s"', $node->getBoost());
            } elseif ($node->useFuzzy()) {
                $tag .= sprintf(' fuzzy="%s"', $node->getFuzzy());
            } elseif ($node instanceof Word && $node->hasTrailingWildcard()) {
                $tag .= ' trailing_wildcard="true"';
            }
        }

        $snaked = trim(strtolower(preg_replace('/([A-Z])/', '_$1', $rule)), '_');
        $tag .= sprintf(' rule="%s"', $snaked);

        if ($node instanceof Numbr || $node instanceof Date) {
            $comparisonOperator = match ($node->getComparisonOperator()) {
                ComparisonOperator::GT => 'gt',
                ComparisonOperator::GTE => 'gte',
                ComparisonOperator::LT => 'lt',
                ComparisonOperator::LTE => 'lte',
                default => null,
            };

            if (null !== $comparisonOperator) {
                $tag .= sprintf(' comparison_operator="%s"', $comparisonOperator);
            }
        }

        $value = (string)$node->getValue();
        if (preg_match('/[^a-zA-Z0-9\s!@#$%\^\*\(\)_\-+"\'\\{\}:;\?\.]+/', $value)) {
            $value = '<![CDATA[' . $value . ']]>';
        }

        $this->printLine(sprintf('<%s>%s</%s>', $tag, $value, $node::NODE_TYPE));
    }

    protected function printLine(string $line, bool $newLine = true): void
    {
        $this->result .= str_repeat(' ', $this->indent) . $line . ($newLine ? PHP_EOL : '');
    }

    protected function indent(int $step = 2): void
    {
        $this->indent += $step;
    }

    protected function outdent(int $step = 2): void
    {
        $this->indent -= $step;
    }
}
