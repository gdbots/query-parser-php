<?php

namespace Gdbots\QueryParser\Builder;

use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Range;
use Gdbots\QueryParser\Node\Subquery;

class ElasticaQueryBuilder extends AbstractQueryBuilder
{
    /**
     * @return static
     */
    public function clear()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function toQuery()
    {
        return '';
    }

    /**
     * @param Field $field
     * @param bool $cacheable
     */
    protected function startField(Field $field, $cacheable = false)
    {
    }

    /**
     * @param Field $field
     * @param bool $cacheable
     */
    protected function endField(Field $field, $cacheable = false)
    {
    }

    /**
     * @param Range $range
     * @param Field $field
     * @param bool $cacheable
     */
    protected function handleRange(Range $range, Field $field, $cacheable = false)
    {

    }

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function startSubquery(Subquery $subquery, Field $field = null)
    {
    }

    /**
     * @param Subquery $subquery
     * @param Field|null $field
     */
    protected function endSubquery(Subquery $subquery, Field $field = null)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function mustMatch(Node $node, Field $field = null)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function shouldMatch(Node $node, Field $field = null)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function mustNotMatch(Node $node, Field $field = null)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    protected function mustMatchTerm(Node $node, Field $field = null, $cacheable = false)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     */
    protected function shouldMatchTerm(Node $node, Field $field = null)
    {
    }

    /**
     * @param Node $node
     * @param Field|null $field
     * @param bool $cacheable
     */
    protected function mustNotMatchTerm(Node $node, Field $field = null, $cacheable = false)
    {
    }
}
