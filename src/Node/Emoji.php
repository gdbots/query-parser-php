<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Emoji extends Node
{
    const NODE_TYPE = 'emoji';

    /**
     * Emoji constructor.
     *
     * @param string       $value
     * @param BoolOperator $boolOperator
     * @param bool         $useBoost
     * @param float        $boost
     */
    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost);
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $value = isset($data['value']) ? $data['value'] : '';
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost);
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addEmoji($this);
    }
}
