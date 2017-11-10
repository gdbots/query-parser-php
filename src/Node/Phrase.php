<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Phrase extends Node
{
    const NODE_TYPE = 'phrase';
    const SUPPORTS_FUZZY = true;

    /**
     * Phrase constructor.
     *
     * @param string       $value
     * @param BoolOperator $boolOperator
     * @param bool         $useBoost
     * @param float        $boost
     * @param bool         $useFuzzy
     * @param int          $fuzzy
     */
    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function fromArray(array $data = [])
    {
        $value = isset($data['value']) ? $data['value'] : '';
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost = isset($data['boost']) ? (float)$data['boost'] : static::DEFAULT_BOOST;
        $useFuzzy = isset($data['use_fuzzy']) ? (bool)$data['use_fuzzy'] : false;
        $fuzzy = isset($data['fuzzy']) ? (int)$data['fuzzy'] : static::DEFAULT_FUZZY;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addPhrase($this);
    }
}
